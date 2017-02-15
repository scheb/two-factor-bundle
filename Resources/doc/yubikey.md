Yubikey Authentication
======================

## How it works ##

The user entity has to be linked with a yubikey first.
This is done by storing a validated identifier of the yubikey it in the user entity.

On successful authentication the bundle checks if there is an identifier stored in the user entity.
If that's the case it will ask for the authentication code. The user must press their yubikey button to gain access.

For more information see the [Yubico website](http://www.yubico.com).


## Basic Configuration ##

To enable this authentication method add this to your config.yml:

```yaml
scheb_two_factor:
    yubikey:
        enabled: true
```

Your user entity has to implement `Scheb\TwoFactorBundle\Model\Yubikey\TwoFactorInterface`. The yubikey identifier must be persisted, so make sure that it is stored in a persisted field.

```php
namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Yubikey\TwoFactorInterface;

class User implements TwoFactorInterface
{
    /**
     * @ORM\Column(name="yubikeyId", type="string", nullable=true)
     */
    private $yubikeyId;

    // [...]

    public function getYubikeyId()
    {
        return $this->yubikeyId;
    }

    public function setYubikeyId($yubikeyId)
    {
        $this->yubikeyId = $yubikeyId;
    }
}
```

This module requires the [SURFnet/yubikey-api-client-bundle](https://github.com/SURFnet/yubikey-api-client-bundle/).

Don't forget to register it in the configuration :

```yaml
surfnet_yubikey_api_client:
    credentials:
        client_id:
        client_secret:
```

The `client_id` and `client_secret` can be generated with a yubikey on this website: https://upgrade.yubico.com/getapikey/

## Custom Template ##

The bundle uses `Resources/views/Authentication/form.html.twig` to render the authentication form. If you want to use a different template you can simply register it in configuration:

```yaml
scheb_two_factor:
    yubikey:
        template: AcmeDemoBundle:Authentication:my_custom_template.html.twig
```


## Registering a yubikey ##

The user has to provide one token from the yubikey, you can use the service below to valide the token and then you can persist it to the user.

The service `scheb_two_factor.security.yubikey.register` provides a method to validate a new yubikey Id for an user.

```php
$yubikeyId = $container->get("scheb_two_factor.security.yubikey.register")->getYubikeyId($userProvidedToken);

$user->setYubikeyId($yubikeyId);
```



