<?php
namespace Scheb\TwoFactorBundle\Command;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FOS\UserBundle\Model\User;

class SendGoogleSecretQrCommand extends ContainerAwareCommand
{

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    public function configure()
    {
        $this
            ->setName("scheb:two-factor:send-google-secret-qr")
            ->setDescription("Send a QR code for Google Authenticator to user by email.")
            ->addArgument('username', InputArgument::REQUIRED, "Username");
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        if (!$container->has("scheb_two_factor.security.google_authenticator")) {
            throw new \RuntimeException("Google Authenticator two-factor authentication is not enabled.");
        }

        if (!$container->has("fos_user.user_provider.username")) {
            throw new \RuntimeException("This feature now works only with FOSUserBundle.");
        }

        $googleAuthenticator = $container->get("scheb_two_factor.security.google_authenticator");

        /** @var TwoFactorInterface|User $user */
        $user = $container
            ->get("fos_user.user_provider.username")
            ->loadUserByUsername($input->getArgument("username"));

        if (empty($user->getGoogleAuthenticatorSecret())) {
            $dialog = $this->getHelper('dialog');
            if (!$dialog->askConfirmation(
                $output,
                '<question>User has not set a secret, do you wish to generate some automatically?</question>',
                true
            )
            ) {
                throw new \RuntimeException("You can't continue without secret.");
            }

            // Generate secret and save it to user instance
            $secret = $googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $em = $container->get('doctrine')->getManager();
            $em->flush();
        }

        $mailer = $container->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject("Google QR")
            ->setFrom($container->getParameter('mailer_user'), $container->getParameter('scheb_two_factor.google.issuer'))
            ->setTo($user->getEmail())
            ->setBody(
                $container->get('twig')->render(
                    $container->getParameter('scheb_two_factor.google.email_template'),
                    array(
                        'qr'          => $googleAuthenticator->getUrl($user),
                        'user'        => $user,
                        'issuer'      => $container->getParameter('scheb_two_factor.google.issuer'),
                        'server_name' => $container->getParameter('scheb_two_factor.google.server_name')
                    )
                ),
                "text/html",
                "UTF-8"
            );

        $mailer->send($message);

        $output->writeln(sprintf("<info>Message sent to %s.</info>", $user->getEmail()));

    }
}
