<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ASABaseBackendController;
use App\Libraries\Mandrill;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Mail;

class MailController extends ASABaseBackendController {

    public function app_launch() {
        \App\Http\Controllers\MailController::send([
            'to' => ['vishal.kumar@gmail.com', 'tekkenbob93@gmail.com', 'amit@gmail.com', 'aanchal.kumar@gmail.com', 'priyanka.sharma@gmail.com', 'chetan@gmail.com'],
            'from' => 'support@gmail.com',
            'subject' => 'Perfect Countdown Sales Timer 2.0 Is Coming',
            'template' => 'email_templates.app_features',
            'template_data' => [
                'type' => 'app',
                'shop' => 'test.com',
                'shop_name' => 'test store',
                'app_code' => 'timer',
                'app_name' => 'Perfect Countdown Sales Timer',
                'email' => 'amit@gmail.com',
            ],
        ]);
        exit;
    }

    public function test() {
        \App\Http\Controllers\MailController::send([
//            'to' => ['vishal.kumar@gmail.com', 'amit.kapoor@gmail.com'],
            'to' => ['vishal.kumar@gmail.com', 'tekkenbob93@gmail.com', 'amit@gmail.com'],
            'from' => 'support@gmail.com',
            'subject' => 'Perfect Countdown Sales Timer: Uninstalled from your Shopify store',
            'template' => 'email_templates.app_features',
            'template_data' => [
                'type' => 'app',
                'shop' => 'test.com',
                'shop_name' => 'test store',
                'app_code' => 'metafields',
                'app_name' => 'Perfect Metafields',
            ],
        ]);
        exit;
    }

    public function view() {

        return view('email_templates.app_features', [
            'type' => 'app',
            'shop_name' => "test",
            'shop' => 'test.com',
            'app_code' => 'metafields',
            'app_name' => 'Perfect Metafields',
            'email' => 'amit@gmail.com',
        ]);
    }

    public static function send($options = []) {
//        $options['to'][] = 'er.priyanka.ps07@gmail.com';
        if (!empty($options['html'])) {
            $r = \Mail::send([], [], function ($message) use ($options) {
                        $message->to($options['to'])
                                ->bcc('amit@gmail.com')
                                ->from((empty($options['from']) ? 'support@gmail.com' : $options['from']), (isset($options['from_name']) && !empty($options['from_name']) ? $options['from_name'] : 'Support@Alliance'))
                                ->subject($options['subject'])
                                ->setBody($options['html'], 'text/html');
                    });
        }

        if (isset($options['template'])) {
            $r = \Mail::send($options['template'], (isset($options['template_data']) ? $options['template_data'] : []), function ($message) use ($options) {
                        $message->to($options['to'])
                                ->bcc('amit@gmail.com')
                                ->from((empty($options['from']) ? 'support@gmail.com' : $options['from']), (isset($options['from_name']) && !empty($options['from_name']) ? $options['from_name'] : 'Support@Alliance'))
                                ->subject($options['subject']);
                    });
        }
        return true;
    }

    public static function send_mail($to, $from, $subject, $html, $file = '', $options = []) {
        $options['attach_file_url'] = (isset($options['attach_file_url'])) ? $options['attach_file_url'] : false;
//        public function sendmail($to, $subject, $body, $from = '') {
        //        if ($from == ''):
        //            $from = 'noreply@gmail.com';
        //        endif;
        //        $mail = new MailController();
        //        return $mail->send_mail($to, $from, $subject, $body);
        //        // return $mail->send_mail('sumitchauhan9807666@gmail.com','noreply@gmail.com','subject','body');
        //    }

        $data = array(
            'message' => array(
                'html' => $html,
                'subject' => $subject,
                'from_email' => (empty($from) ? 'noreply@gmail.com' : $from),
                'from_name' => (isset($options['from_name']) && !empty($options['from_name']) ? $options['from_name'] : 'Alliance Ecommerce Partners'),
                'to' => array(array('email' => $to)),
            ),
        );

        if (!empty($file) && sizeof($file) && $options['attach_file_url'] == false) {
            if (isset($file[0])) {
                foreach ($file as $key => $_file) {
                    $file_size = $_file->getClientSize();
                    $handle = fopen($_file->getPathName(), "r");
                    $content = fread($handle, $file_size);
                    fclose($handle);
                    $data['message']['attachments'][] = array(
                        'type' => 'text/plain',
                        'name' => 'attachment' . $key . '.' . $_file->getClientOriginalExtension(),
                        'content' => base64_encode($content),
                    );
                }
            } else {
                $file_size = $file->getClientSize();
                $handle = fopen($file->getPathName(), "r");
                $content = fread($handle, $file_size);
                fclose($handle);
                $data['message']['attachments'] = array(
                    array(
                        'type' => 'text/plain',
                        'name' => 'attachment.' . $file->getClientOriginalExtension(),
                        'content' => base64_encode($content),
                    ),
                );
            }
        } elseif (!empty($file) && $options['attach_file_url'] == true) {
            foreach ($file as $_file) {
                $attachment = file_get_contents($_file['file_url']);
                $attachment_encoded = base64_encode($attachment);
                $data['message']['attachments'][] = array(
                    'content' => $attachment_encoded,
                    'type' => $_file['file_type'],
                    'name' => $_file['file_name'],
                );
            }
        }

        $response = Mandrill::request('messages/send', $data);

        return json_encode($response);
    }

    public static function send_multiple_email($to, $from, $subject, $html, $file = '', $options = []) {
        $data = array(
            'message' => array(
                'html' => $html,
                'subject' => $subject,
                'from_email' => (empty($from) ? 'noreply@gmail.com' : $from),
                'from_name' => (isset($options['from_name']) && !empty($options['from_name']) ? $options['from_name'] : 'Alliance Ecommerce Partners'),
                'to' => $to,
                'headers' => (isset($options['reply_to']) && !empty($options['reply_to']) ? array('Reply-To' => $options['reply_to']) : ''),
            ),
        );

        if (!empty($file) && sizeof($file)) {
            if (isset($file[0])) {
                foreach ($file as $key => $_file) {
                    $file_size = $_file->getClientSize();
                    $handle = fopen($_file->getPathName(), "r");
                    $content = fread($handle, $file_size);
                    fclose($handle);
                    $data['message']['attachments'][] = array(
                        'type' => 'text/plain',
                        'name' => 'attachment' . $key . '.' . $_file->getClientOriginalExtension(),
                        'content' => base64_encode($content),
                    );
                }
            } else {
                $file_size = $file->getClientSize();
                $handle = fopen($file->getPathName(), "r");
                $content = fread($handle, $file_size);
                fclose($handle);
                $data['message']['attachments'] = array(
                    array(
                        'type' => 'text/plain',
                        'name' => 'attachment.' . $file->getClientOriginalExtension(),
                        'content' => base64_encode($content),
                    ),
                );
            }
        }

        $response = Mandrill::request('messages/send', $data);

        return json_encode($response);
    }

    public static function userVerificationToken() {
        $unixTimestamp = time();
        $d = strtotime(date("Y-m-d H:i:s", $unixTimestamp) . '+7 days');
        $token = strtolower(Str::random(64)) . '*$%' . base64_encode($d);
        return $token;
    }

    public static function userVarificationMail($data) {
        $verificationUrl = base_url() . '/verify?t=' . $data['token'];
        $to = $data['email'];
        $from = 'noreply@gmail.com';
        $subject = 'Registration';
//           $logo = (get_settings_data('Settings', 'logo'))?url(aec_get_images_base_path().'upload/app/logo/'.get_settings_data('Settings', 'logo')):'';
        $baseUrl = env('APP_URL');
        $logo = 'https://sa.aec-clients.co.in/partnerfrontend/logo_main.png';
        $body = '<style>
                    @media only screen and (max-width: 480px){
                        .d-sm-none{display:none;}
                    }
                </style>
                <table style="border-spacing: 0;width:600px;max-width:100%;margin:auto;font-family: Montserrat;">
                    <tr>
                        <td style="font-family: Montserrat;padding: 10px 0;">
                            <a target="_blank" href="https://sa.aec-clients.co.in">
                                <img class="logo-img" src="' . $logo . '" width="250px" style="margin: auto;display: block;" />
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-family: Montserrat;padding:0;display: inline-flex;">
                            <img src="https://sa.aec-clients.co.in/partnerfrontend/bg-image.png" width="100%" />
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:35px 20px;font-family: Montserrat;background-color: #ecf5ff;">
                            <table style="border-spacing: 0;width:100%;text-align: center;">
                                <tr>
                                    <td style="font-family: Montserrat;">
                                        <h2 style="color: #000000;font-size: 26px;font-weight: bold;margin-bottom: 23px;margin-top: 0;line-height: 20px;">
                                        Hi, ' . ucfirst($data['name']) . '
                                        </h2>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family: Montserrat;">
                                        <p style="font-family: Montserrat;font-size: 16px;line-height: 20px;margin-top:0;margin-bottom: 37px;">You are registered successfully.<br>Please click here to verify your email.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <a style="font-family: Montserrat;font-size: 16px;font-weight: 600;background-color: #01a4ec;border-radius: 10px;text-decoration:none;color: #fff;
                                        padding: 18px 70px;" href="' . $verificationUrl . '">ACTIVATE</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family: Montserrat;">
                                        <p style="font-family: Montserrat;font-size: 16px;line-height: 20px;margin-top:37px;margin-bottom: 0;">Need more help? email to
                                            <br><a style="font-family: Montserrat;color:#000;font-size: 16px;" href="mailto:info@gmail.com">info@gmail.com</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:35px 20px;font-family: Montserrat;background-color: #004a99;">
                            <table style="width:100%;text-align: center;color: #fff;">
                                <tr>
                                    <td style="font-family: Montserrat;padding:0;">
                                        <a style="display:block;font-family: Montserrat;color: #fff;font-size: 24px;font-weight: bold;text-transform: uppercase;text-decoration:none;line-height: 20px;" target="_blank" href="https://sa.aec-clients.co.in">
                                            Alliance Ecommerce
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family: Montserrat;">
                                        <p style="font-family: Montserrat;font-size: 16px;line-height: 20px;margin-top:15px;margin-bottom: 0;">
                                            Sebiz Business Center, 2nd floor , C/o Sebiz Square Plot C 6,<br class="d-sm-none">
                                            IT Park Road, Sector 67, Sahibzada Ajit Singh Nagar, Punjab 160062
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family: Montserrat;">
                                        <ul style="list-style: none;padding: 0;margin-top: 15px;margin-bottom: 0;">
                                            <li style="display: inline-block;margin-left: 0;margin-right: 30px;">
                                                <a style="display:block;font-family: Montserrat;color: #fff;font-size: 16px;text-transform: uppercase;text-decoration:none;line-height: 20px;" target="_blank" href="#">Help</a>
                                            </li>
                                            <li style="display: inline-block;margin-left: 0;margin-right: 30px;">
                                                <a style="display:block;font-family: Montserrat;color: #fff;font-size: 16px;text-transform: uppercase;text-decoration:none;line-height: 20px;" target="_blank" href="#">Privcay</a>
                                            </li>
                                            <li style="display: inline-block;margin-left: 0;">
                                                <a style="display:block;font-family: Montserrat;color: #fff;font-size: 16px;text-transform: uppercase;text-decoration:none;line-height: 20px;" target="_blank" href="#">Terms</a>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td clsss="bottom-line" style="font-family: Montserrat;border-bottom: 2px solid #fff;margin: 20px 0;display: block;"></td>
                                </tr>
                                <tr>
                                    <td style="font-family: Montserrat;">
                                        <h2 style="font-family: Montserrat;font-size: 16px;font-weight: bold;margin-bottom: 15px;">
                                            Connect with us
                                        </h2>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family: Montserrat;">
                                        <ul style="list-style: none;padding: 0;margin: 0;">
                                            <li style="display: inline-block;margin-left: 0;margin-right: 30px;">
                                                <a target="_blank" href="https://www.facebook.com/AllianceEcommerce.in/">
                                                    <img src="https://sa.aec-clients.co.in/partnerfrontend/facebook.png" width="36px" />
                                                </a>
                                            </li>
                                            <li style="display: inline-block;margin-left: 0;margin-right: 30px;">
                                                <a target="_blank" href="https://www.instagram.com/theofficialallianceecommerce/">
                                                    <img src="https://sa.aec-clients.co.in/partnerfrontend/instagram.png" width="36px" />
                                                </a>
                                            </li>
                                            <li style="display: inline-block;margin-left: 0;">
                                                <a target="_blank" href="https://linkedin.com/company/allianceecommerce/">
                                                    <img src="https://sa.aec-clients.co.in/partnerfrontend/linkedin.png" width="36px" />
                                                </a>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p style="font-family: Montserrat;font-size: 14px;line-height: 20px;margin-top:25px;margin-bottom: 0;">
                                            © 2020 Alliance Ecommerce. All Rights Reserved
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>';
        self::send_mail($to, $from, $subject, $body);
    }

    /**
     *
     * @param type $data
     * this email link is valid for 30 minutes
     */
    public static function resetPasswordEmail($data) {
        $t = time();
        $d = strtotime('+30 minutes', $t);
        $token = strtolower(Str::random(64)) . '*$%' . base64_encode($d);
//        return $request->email;
        User::where('email', $data['email'])->update(['token' => $token]);
        $url = URL::to('') . '/newpassword?t=' . $token;

        $to = $data['email'];
        $from = 'noreply@gmail.com';
        $subject = 'Reset Password';
        $baseUrl = env('APP_URL');
        $logo = 'https://sa.aec-clients.co.in/partnerfrontend/logo_main.png';
        $body = '<style>
                    @media only screen and (max-width: 480px){
                        .d-sm-none{display:none;}
                    }
                </style>
                <table style="border-spacing: 0;width:600px;max-width:100%;margin:auto;font-family: Montserrat;">
                    <tr>
                        <td style="font-family: Montserrat;padding: 10px 0;">
                            <a target="_blank" href="https://sa.aec-clients.co.in">
                                <img class="logo-img" src="' . $logo . '" width="250px" style="margin: auto;display: block;" />
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-family: Montserrat;padding:0;display: inline-flex;">
                            <img src="https://sa.aec-clients.co.in/partnerfrontend/bg-image-1.png" width="100%" />
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:35px 20px;font-family: Montserrat;background-color: #ecf5ff;">
                            <table style="border-spacing: 0;width:100%;text-align: center;">
                                <tr>
                                    <td style="font-family: Montserrat;">
                                        <p style="font-family: Montserrat;font-size: 16px;line-height: 20px;margin-top:0;margin-bottom: 37px;">Seems like you forgot your password If this is true,<br> click below to reset your password..</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <a style="font-family: Montserrat;font-size: 16px;font-weight: 600;background-color: #01a4ec;border-radius: 10px;text-decoration:none;color: #fff;padding: 18px 30px;text-transform:uppercase;" href="' . $url . '">Reset My Password</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family: Montserrat;">
                                        <p style="font-family: Montserrat;font-size: 16px;line-height: 20px;margin-top:37px;margin-bottom: 0;">If you did not forgot your password  you can safely ignore this email<br><br> Need more help? email to <br><a style="font-family: Montserrat;color:#000;font-size: 16px;" href="mailto:info@gmail.com">info@gmail.com</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:35px 20px;font-family: Montserrat;background-color: #004a99;">
                            <table style="width:100%;text-align: center;color: #fff;">
                                <tr>
                                    <td style="font-family: Montserrat;padding:0;">
                                        <a style="display:block;font-family: Montserrat;color: #fff;font-size: 24px;font-weight: bold;text-transform: uppercase;text-decoration:none;line-height: 20px;" target="_blank" href="https://sa.aec-clients.co.in">
                                            Alliance Ecommerce
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family: Montserrat;">
                                        <p style="font-family: Montserrat;font-size: 16px;line-height: 20px;margin-top:15px;margin-bottom: 0;">
                                            Sebiz Business Center, 2nd floor , C/o Sebiz Square Plot C 6,<br class="d-sm-none">
                                            IT Park Road, Sector 67, Sahibzada Ajit Singh Nagar, Punjab 160062
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family: Montserrat;">
                                        <ul style="list-style: none;padding: 0;margin-top: 15px;margin-bottom: 0;">
                                            <li style="display: inline-block;margin-left: 0;margin-right: 30px;">
                                                <a style="display:block;font-family: Montserrat;color: #fff;font-size: 16px;text-transform: uppercase;text-decoration:none;line-height: 20px;" target="_blank" href="#">Help</a>
                                            </li>
                                            <li style="display: inline-block;margin-left: 0;margin-right: 30px;">
                                                <a style="display:block;font-family: Montserrat;color: #fff;font-size: 16px;text-transform: uppercase;text-decoration:none;line-height: 20px;" target="_blank" href="#">Privcay</a>
                                            </li>
                                            <li style="display: inline-block;margin-left: 0;">
                                                <a style="display:block;font-family: Montserrat;color: #fff;font-size: 16px;text-transform: uppercase;text-decoration:none;line-height: 20px;" target="_blank" href="#">Terms</a>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td clsss="bottom-line" style="font-family: Montserrat;border-bottom: 2px solid #fff;margin: 20px 0;display: block;"></td>
                                </tr>
                                <tr>
                                    <td style="font-family: Montserrat;">
                                        <h2 style="font-family: Montserrat;font-size: 16px;font-weight: bold;margin-bottom: 15px;">
                                            Connect with us
                                        </h2>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family: Montserrat;">
                                        <ul style="list-style: none;padding: 0;margin: 0;">
                                            <li style="display: inline-block;margin-left: 0;margin-right: 30px;">
                                                <a target="_blank" href="https://www.facebook.com/AllianceEcommerce.in/">
                                                    <img src="https://sa.aec-clients.co.in/partnerfrontend/facebook.png" width="36px" />
                                                </a>
                                            </li>
                                            <li style="display: inline-block;margin-left: 0;margin-right: 30px;">
                                                <a target="_blank" href="https://www.instagram.com/theofficialallianceecommerce/">
                                                    <img src="https://sa.aec-clients.co.in/partnerfrontend/instagram.png" width="36px" />
                                                </a>
                                            </li>
                                            <li style="display: inline-block;margin-left: 0;">
                                                <a target="_blank" href="https://linkedin.com/company/allianceecommerce/">
                                                    <img src="https://sa.aec-clients.co.in/partnerfrontend/linkedin.png" width="36px" />
                                                </a>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p style="font-family: Montserrat;font-size: 14px;line-height: 20px;margin-top:25px;margin-bottom: 0;">
                                            © 2020 Alliance Ecommerce. All Rights Reserved
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>';
        self::send_mail($to, $from, $subject, $body);
    }

}
