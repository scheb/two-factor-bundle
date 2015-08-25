Google Authentication
====================

## How it works ##

The user entity has to be linked with Google Authenticator first. This is done by generating a secret code and storing it in the user entity. Users can add that code to the Google Authenticator app on their mobile. The app will generate a 6-digit numeric code from it that changes every 30 seconds.

On successful authentication the bundle checks if there is a secret stored in the user entity. If that's the case it will ask for the authentication code. The user must enter the code currently shown in the Google Authenticator app to gain access.

For more information see the [Google Authenticator website](http://code.google.com/p/google-authenticator/).


## Basic Configuration ##

To enable this authentication method add this to your config.yml:

```yaml
scheb_two_factor:
    google:
        enabled: true
```

Your user entity has to implement `Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface`. The secret code must be persisted, so make sure that it is stored in a persisted field.

```php
namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

class User implements TwoFactorInterface
{
    
    /**
     * @ORM\Column(type="googleAuthenticatorSecret", nullable=true)
     */
    private $googleAuthenticatorSecret;
    
    // [...]
    
    public function getGoogleAuthenticatorSecret() {
        return $this->googleAuthenticatorSecret;
    }

    public function setGoogleAuthenticatorSecret($googleAuthenticatorSecret) {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
    }
}
```


## Custom Template ##

The bundle uses `Resources/views/Authentication/form.html.twig` to render the authentication form. If you want to use a different template you can simply register it in configuration: 

```yaml
scheb_two_factor:
    google:
        template: AcmeDemoBundle:Authentication:my_custom_template.html.twig
```


## Generating a Secret Code ##

The service `scheb_two_factor.security.google_authenticator` provides a method to generate new secret for Google Authenticator.

```php
$secret = $container->get("scheb_two_factor.security.google_authenticator")->generateSecret();
```

There is also a console command to generates new codes:

```bash
php app/console scheb:two-factor:google-secret
```


## QR Codes ##

If a user entity has a secret code stored, you can generate a nice-looking QR code from it that can be scanned by the Google Authenticator app.

```php
$url = $container->get("scheb_two_factor.security.google_authenticator")->getUrl($user);
echo '<img src="'.$url.'" />';
```

### Send QR code to user ###



You can also send QR code to user by email. Email contains code and useful links to Google Play store, Apple store etc. This feature works only with [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle).

```bash
php app/console scheb:two-factor:send-google-secret-qr username
```

## Twig Extension ##

You can render QR code for logged in user also in template.

```
<img src="{{ googleAuthenticatorQrUrl() }}">
```

Or render QR code for some other user injecting user object.

```
<img src="{{ googleAuthenticatorQrUrl($user) }}">
```
