<?php

namespace App\Helpers;

/**
 * Email helper for sending notifications
 * Uses PHP's mail() function - configure SMTP for production
 */
class Email
{
    private $from;
    private $fromName;

    public function __construct()
    {
        $this->from = Env::get('MAIL_FROM_ADDRESS', 'noreply@example.com');
        $this->fromName = Env::get('MAIL_FROM_NAME', 'Blog CMS');
    }

    /**
     * Send email
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $message Email body (HTML)
     * @param array $headers Additional headers
     * @return bool
     */
    public function send($to, $subject, $message, $headers = [])
    {
        try {
            // Build headers
            $defaultHeaders = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=utf-8',
                "From: {$this->fromName} <{$this->from}>",
                'X-Mailer: PHP/' . phpversion()
            ];

            $allHeaders = array_merge($defaultHeaders, $headers);
            $headerString = implode("\r\n", $allHeaders);

            // Wrap message in HTML template
            $htmlMessage = $this->wrapInTemplate($message);

            // Send email
            $result = mail($to, $subject, $htmlMessage, $headerString);

            if ($result) {
                Logger::info('Email sent successfully', ['to' => $to, 'subject' => $subject]);
            } else {
                Logger::error('Email send failed', ['to' => $to, 'subject' => $subject]);
            }

            return $result;
        } catch (\Exception $e) {
            Logger::error('Email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send password reset email
     *
     * @param string $to Recipient email
     * @param string $username Username
     * @param string $resetLink Reset link URL
     * @return bool
     */
    public function sendPasswordReset($to, $username, $resetLink)
    {
        $subject = 'Password Reset Request';

        $message = "
            <h2>Password Reset Request</h2>
            <p>Hello {$username},</p>
            <p>We received a request to reset your password. Click the link below to reset your password:</p>
            <p><a href=\"{$resetLink}\" style=\"background-color: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;\">Reset Password</a></p>
            <p>Or copy and paste this link into your browser:</p>
            <p>{$resetLink}</p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request a password reset, you can safely ignore this email.</p>
            <p>Thanks,<br>The Blog CMS Team</p>
        ";

        return $this->send($to, $subject, $message);
    }

    /**
     * Send welcome email to new user
     *
     * @param string $to Recipient email
     * @param string $username Username
     * @return bool
     */
    public function sendWelcome($to, $username)
    {
        $subject = 'Welcome to ' . Env::get('APP_NAME', 'Blog CMS');
        $appUrl = Env::get('APP_URL', '');

        $message = "
            <h2>Welcome to Blog CMS!</h2>
            <p>Hello {$username},</p>
            <p>Thank you for registering an account with us. We're excited to have you on board!</p>
            <p>You can now:</p>
            <ul>
                <li>Create and publish blog posts</li>
                <li>Comment on articles</li>
                <li>Customize your profile</li>
                <li>Connect with other members</li>
            </ul>
            <p><a href=\"{$appUrl}/public/login.php\" style=\"background-color: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;\">Get Started</a></p>
            <p>Thanks,<br>The Blog CMS Team</p>
        ";

        return $this->send($to, $subject, $message);
    }

    /**
     * Send comment notification to post author
     *
     * @param string $to Post author email
     * @param string $postTitle Post title
     * @param string $commenterName Commenter name
     * @param string $commentContent Comment content
     * @param string $postUrl Post URL
     * @return bool
     */
    public function sendCommentNotification($to, $postTitle, $commenterName, $commentContent, $postUrl)
    {
        $subject = 'New Comment on Your Post: ' . $postTitle;

        $message = "
            <h2>New Comment on Your Post</h2>
            <p>Someone commented on your post \"<strong>{$postTitle}</strong>\"</p>
            <div style=\"background-color: #f8f9fa; padding: 15px; border-left: 4px solid #0d6efd; margin: 20px 0;\">
                <p><strong>{$commenterName}</strong> said:</p>
                <p>{$commentContent}</p>
            </div>
            <p><a href=\"{$postUrl}\" style=\"background-color: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;\">View Comment</a></p>
        ";

        return $this->send($to, $subject, $message);
    }

    /**
     * Wrap message in HTML template
     *
     * @param string $content Email content
     * @return string
     */
    private function wrapInTemplate($content)
    {
        $appName = Env::get('APP_NAME', 'Blog CMS');

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset=\"UTF-8\">
            <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
            <title>{$appName}</title>
        </head>
        <body style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;\">
            <div style=\"background-color: #0d6efd; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;\">
                <h1 style=\"margin: 0;\">{$appName}</h1>
            </div>
            <div style=\"background-color: #ffffff; padding: 30px; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 5px 5px;\">
                {$content}
            </div>
            <div style=\"text-align: center; padding: 20px; color: #6c757d; font-size: 12px;\">
                <p>&copy; " . date('Y') . " {$appName}. All rights reserved.</p>
            </div>
        </body>
        </html>
        ";
    }
}
