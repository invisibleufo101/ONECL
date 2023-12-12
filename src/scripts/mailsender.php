<?php
//Load dependencies from composer
//If this causes an error, run 'composer install'
require 'PHPMailer/vendor/autoload.php';
require '../../vendor/autoload.php';

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;

//Alias the League Google OAuth2 provider class
use League\OAuth2\Client\Provider\Google;

if(isset($_POST['submit'])){
    
    $dotenv = Dotenv\Dotenv::createImmutable('../../');
    $dotenv->load();
    
    //SMTP needs accurate times, and the PHP time zone MUST be set
    //This should be done in your php.ini, but this is how to do it if you don't have access to that
    date_default_timezone_set('Etc/UTC');

    //Create a new PHPMailer instance
    $mail = new PHPMailer();

    //Tell PHPMailer to use SMTP
    $mail->isSMTP();

    //Enable SMTP debugging
    //SMTP::DEBUG_OFF = off (for production use)
    //SMTP::DEBUG_CLIENT = client messages
    //SMTP::DEBUG_SERVER = client and server messages
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;

    //Set the hostname of the mail server
    $mail->Host = 'smtp.gmail.com';

    //Set the SMTP port number:
    // - 465 for SMTP with implicit TLS, a.k.a. RFC8314 SMTPS or
    // - 587 for SMTP+STARTTLS
    $mail->Port = 465;

    //Set the encryption mechanism to use:
    // - SMTPS (implicit TLS on port 465) or
    // - STARTTLS (explicit TLS on port 587)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;

    //Set AuthType to use XOAUTH2
    $mail->AuthType = 'XOAUTH2';

    //Fill in authentication details here
    //Either the gmail account owner, or the user that gave consent
    $email = 'freelock.export2@gmail.com';
    $clientId = $_ENV['CLIENT_ID'];
    $clientSecret = $_ENV['CLIENT_SECRET'];

    //Obtained by configuring and running get_oauth_token.php
    //after setting up an app in Google Developer Console.
    $refreshToken = $_ENV['REFRESH_TOKEN'];

    //Create a new OAuth2 provider instance
    $provider = new Google(
        [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ]
    );

    //Pass the OAuth provider instance to PHPMailer
    $mail->setOAuth(
        new OAuth(
            [
                'provider' => $provider,
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'refreshToken' => $refreshToken,
                'userName' => $email,
            ]
        )
    );

    //Set who the message is to be sent from
    //For gmail, this generally needs to be the same as the user you logged in as
    $mail->setFrom($email, 'ONECL MESSENGER');

    //Set who the message is to be sent to
    // $mail->addAddress('weareonecl.us@gmail.com', 'ONECL TEAM');
    $mail->addAddress('ihasdag@gmail.com');

    //Set the subject line
    $mail->Subject = $_POST['subject'];
    
    $mail->Body = "
        Message from ONECL-US.com\n
        Name : " . $_POST['first_name'] . " " . $_POST['last_name'] . "\n
        Email : " . $_POST['email'] . "\n
        Message : \n" . $_POST['message'];

    //send the message, check for errors
    if (!$mail->send()) {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo 'Message sent!';
    }

    ?>
    <script>
        window.alert("Your message has been sent!");
        window.location.href="http://localhost/ONECL/contact.html";
    </script>
    <?php
    exit;
}

?>