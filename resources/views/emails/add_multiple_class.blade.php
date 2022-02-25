<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>O circl</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <style type="text/css">
            @import url(https://fonts.googleapis.com/css?family=Open+Sans:400,700);
            #outlook a {padding: 0;}
            body {width: 100% !important;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;margin: 0;padding: 0;background: #dedede!important;color: #000!important;font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif!important;}
            .ExternalClass {width: 100%;}
            .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}
            #backgroundTable {margin: 0;padding: 0;width: 100% !important;line-height: 100% !important;}
            img {outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;border: none;}
            a img {border: none;}
            .image_fix {display: block;}
            p {margin: 1em 0;}
            h1, h2, h3, h4, h5, h6 {color: #fcbf31 !important;}
            h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {color: #99cc33 !important;}
            h1 a:active, h2 a:active, h3 a:active, h4 a:active, h5 a:active, h6 a:active {color: #99cc33 !important;}
            h1 a:visited, h2 a:visited, h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited {color: purple !important;}
            table td {border-collapse: collapse;}
            table {border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;}
            a {color: orange;}
            span a {color: #000000!important;}
            .visibleMobile { display:block;}
            .visibleMobile {display: none;}
            html { width: 100%; }
            p { padding: 0 !important; margin-top: 0 !important; margin-right: 0 !important; margin-bottom: 0 !important; margin-left: 0 !important; }
            .visibleMobile { display: none; }
            .hiddenMobile { display: block; }

            @media only screen and (max-device-width: 480px) {
                a[href^="tel"], a[href^="sms"] {text-decoration: none;color: #000000;pointer-events: none;cursor: default;}
                .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {text-decoration: default;color: #000000 !important;pointer-events: auto;cursor: default;}
            }

            @media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
                a[href^="tel"], a[href^="sms"] {text-decoration: none;color: #000000;pointer-events: none;cursor: default;}
                .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {text-decoration: default;color: #000000 !important;pointer-events: auto;cursor: default;}
            }

            @media only screen and (max-width: 600px) {
                body { width: auto !important; }
                table[class=fullTable] { width: 100% !important; clear: both; }
                table[class=fullPadding] { width: 85% !important; clear: both; }
                .erase { display: none; }
            }

            @media only screen and (max-width: 420px) {
                table[class=fullTable] { width: 100% !important; clear: both; }
                table[class=fullPadding] { width: 85% !important; clear: both; }
                table[class=col] { width: 100% !important; clear: both; }
                table[class=col] td { text-align: left !important; }
                .w100 { width:100%!important; text-align:center; display:none!important;}
            }

        </style>

        <!-- Targeting Windows Mobile -->
        <!--[if IEMobile 7]>
                <style type="text/css">
        
                </style>
                <![endif]-->

        <!--[if gte mso 9]>
                <style>
                        /* Target Outlook 2007 and 2010 */
                </style>
                <![endif]-->
    </head>
    <body style="margin:0;background-color: #cccccc; font-family: arial; padding-top:10px;padding-bottom:10px;">
        <table style="width: 96%; max-width: 600px; margin:0 auto; background-color: #ffffff; border-radius: 10px;" cellspacing="0" cellpadding="0">
            <tbody>
                <tr>
                    <td>
                        <table style="width: 100%; margin: 0 auto; border-radius: 10px 10px 0 0; float: left; background-color: #F9F9F9;" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="width: 100%; float: left; padding-top: 20px; padding-bottom: 20px;"><a href="#" target="_blank"><img src="http://d2bp2kgc0vgu09.cloudfront.net/uploads/general/5ffdd27387c841610470003.png" style="border: none; width: 116px; margin: auto; display: table;" alt="O circl" /></a></td>
                            </tr>
                        </table>
                        <table style="width: 90%; margin: 0 auto; background-color: #ffffff; padding-top: 20px; padding-left: 20px; padding-bottom: 20px; padding-right: 20px;" cellspacing="0" cellpadding="0">
                            <tr><td height="30"></td></tr>			
                            <tr>
                                @if($data['send_to']=="freelancer")
                                <td style="width: 100%; float: left; text-align: left; font-size: 25px; color: #606060; text-transform: capitalize; font-weight: 700;">Hi {{!empty($data['freelancer_detail'])?$data['freelancer_detail']['first_name']:"Freelancer"}}!</td>
                                @endif
                            </tr>

                            <tr>
                                <td style="width: 100%; float: left; text-align: left; font-size: 16px; color: #999999; font-weight: 600; margin-top: 10px; margin-bottom: 10px;">{{$data['message']}}:</td>
                            </tr>

                            <tr><td height="10"></td></tr> 

                            <tr>
                                <td style="width: 100%; float: left; margin-top: 25px; margin-bottom: 10px; text-align: center;">
                                    <a href="#" style="background-color: #43DCBB;color: #ffffff;font-size: 16px;text-align: center;float: left;border-radius: 37px;text-decoration: none;padding-top: 9px;padding-bottom: 9px;padding-left: 25px;padding-right: 25px;">
                                        <img src="http://d2bp2kgc0vgu09.cloudfront.net/uploads/general/6035180209cd31614092290.png" style="border: none; float: left; width: 21px; margin-right: 5px; margin-top: 3px;"><span style="float: left; margin-top: 2px;">Confirmed</span>
                                    </a>
                                </td>
                            </tr>
                            <tr><td height="15"></td></tr>
                        </table>

                        <table style="width: 100%; margin: 0 auto; background-color: #F9F9F9; border-radius: 10px; padding-top: 30px; padding-left: 20px; padding-bottom: 20px; padding-right: 20px;" cellspacing="0" cellpadding="0">
                            <tr>
                                <td>
                                    <table style="width: 90%; margin: 0 auto; background-color: #F9F9F9; border-radius: 10px; padding-top: 30px; padding-left: 20px; padding-bottom: 20px; padding-right: 20px;" cellspacing="0" cellpadding="0">
                                        <tr><td height="25"></td></tr>
                                        <tr>
                                            <!-- Customer Image -->
                                            @if(!empty($data['customer_detail']['profile_image']))                                            
                                            <td style="max-width: 54px;float: left;"><img style="object-fit: cover; max-width:54px; border-radius: 50%;" src="<?= "http://d2bp2kgc0vgu09.cloudfront.net/uploads/profile_images/customers/96/" . $data['customer_detail']['profile_image']; ?>" /></td>
                                            @else
                                            <td style="width: 54px; float: left;"><img src="http://d2bp2kgc0vgu09.cloudfront.net/uploads/general/5ffdd321d3c251610470177.png" style="border-radius: 50px; max-width:54px;" /></td>
                                            @endif

                                            <td style="width: 60%; float: left; padding-left: 10px; padding-top: 3px;">
                                                <table style="width: 100%; float: left;">
                                                    <tbody>
                                                        @if($data['send_to']=="freelancer")
                                                        <tr><td style="width: 100%; float: left; text-align: left; font-size: 20px; color: #606060; text-transform: capitalize; line-height: 13px; font-weight: 700;">{{!empty($data['customer_detail']['first_name']) ? $data['customer_detail']['first_name']: "Customer"}} {{!empty($data['customer_detail']['last_name']) ? $data['customer_detail']['last_name']:null}}</td></tr>
                                                        <tr><td style="width: 100%; float: left; text-align: left; font-size: 16px; color: #999999; font-weight: 600; text-transform: capitalize;">{{$data['package_detail']['package_name']}}</td></tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>

                                        @if(!empty($data['class_data']))
                                        @foreach($data['class_data'] as $index => $class)
                                        @if($index == 0)
                                        <tr style="border-bottom: solid 1px #E3E3E3; padding-bottom: 30px; width: 100%; float: left;"></tr>
                                        @endif

                                        @if(!empty($class))
                                        @if(!empty($class['class_object']['name']))
                                        <tr>
                                            <td style="width: 100px; float: left; margin-top: 23px; font-size: 13px; color: #606060; text-align: left; font-weight: 600; text-transform: capitalize;">booking:</td>
                                            <td style="width: 60%; float: left; margin-top: 20px; font-size: 20px; color: #606060; text-align: left; font-weight: 600;">{{$class['class_object']['name']}}</td>
                                        </tr>
                                        @endif
                                        <!-- Type -->
                                        <?php
                                        $type = "Face-to-Face";
                                        $image = "6050934db61141615893325.png";
                                        if (!empty($class['class_object']['online_link'])) {
                                            $type = "Online";
                                            $image = "60340c8ff3e631614023823.png";
                                        }
                                        ?>
                                        <tr>
                                            <td style="width: 100px; float: left; margin-top: 23px; font-size: 13px; color: #606060; text-align: left; font-weight: 600; text-transform: capitalize;">Type:</td>
                                            <td style="width: 60%; float: left; margin-top: 20px; font-size: 13px; color: #606060; text-align: left; font-weight: 600;"><img src="http://d2bp2kgc0vgu09.cloudfront.net/uploads/general/{{$image}}" style="width: 20px; float: left;" /><span style="float: left;margin-top: 4px;margin-left: 7px;">{{$type}}</span></td>
                                        </tr>
                                        <!-- Duration -->
                                        <tr>
                                            <td style="width: 100px; float: left; margin-top: 20px; font-size: 13px; color: #606060; text-align: left; font-weight: 600; text-transform: capitalize;">Duration:</td>
                                            <td style="width: 60%; float: left; margin-top: 20px; font-size: 13px; color: #606060; text-align: left; font-weight: 600;">{{round($class['duration'])}} minutes</td>
                                        </tr>
                                        <!-- Date/Time -->
                                        <tr>
                                            <td style="width: 100px; float: left; margin-top: 20px; font-size: 13px; color: #606060; text-align: left; font-weight: 600; text-transform: capitalize;">date/time:</td>
                                            <td style="width: 60%; float: left; margin-top: 20px; font-size: 13px; color: #606060; text-align: left; font-weight: 600;">{{$class['day']}} {{$class['date']}} {{$class['month']}} - {{$class['from_time']}} - {{$class['to_time']}} , {{($class['local_timezone'])}}</td>
                                        </tr>

                                        @if(!empty($data['class_data']['class_object']['address']))
                                        <tr style="border-bottom: solid 1px #E3E3E3; padding-bottom: 30px; width: 100%; float: left;">
                                            <td style="width: 100px; float: left; margin-top: 20px; font-size: 13px; color: #606060; text-align: left; font-weight: 600; text-transform: capitalize;">Location:</td>
                                            <td style="width: 60%; float: left; margin-top: 20px; font-size: 13px; color: #606060; text-align: left; font-weight: 600;"><img src="http://d2bp2kgc0vgu09.cloudfront.net/uploads/general/5ffdd3463ce141610470214.png" style="width: 12px;" /> {{ $class['address']}}</td>
                                        </tr>                                    
                                        @endif
                                        <tr style="border-bottom: solid 1px #E3E3E3; padding-bottom: 30px; width: 100%; float: left;"></tr>
                                        @endif
                                        @endforeach
                                        @endif

                                        @if(!empty($data['package_price']))
                                        <tr>
                                            <td style="width: 100px; float: left; margin-top: 22px; font-size: 16px; color: #606060; text-align: left; font-weight: 600; text-transform: capitalize;">Price</td>
                                            <?php $currency = ($data['currency'] == "Pound") ? "£" : $data['currency']; ?>
                                            <td style="width: 60%; float: left; margin-top: 20px; font-size: 20px; color: #606060; text-align: right; font-weight: 700;">{{$currency}} {{$data['package_price']}}</td>
                                        </tr>
                                        @endif
                                        <tr><td height="25"></td></tr>
                                    </table>
                                </td>
                            </tr>						
                        </table>
                        <table style="width: 90%; margin: 0 auto; border-radius: 10px; background-color: #ffffff; padding-top: 20px; padding-left: 20px; padding-bottom: 30px; padding-right: 20px;" cellspacing="0" cellpadding="0">
                            <tbody>
                                <tr><td height="25"></td></tr>
                                <tr>
                                    <td style="width: 100%; float: left; text-align: center; font-size: 16px; color: #999999; font-weight: 600; margin-top: 10px; margin-bottom: 10px;">Have a great class</td>
                                </tr>
                                <tr>
                                    <td style="width: 100%; float: left; margin-top: 25px; margin-bottom: 10px; text-align: center;">
                                        <a href="#" style="background-color: #43DCBB;color: #ffffff;font-size: 18px;text-align: center;display: inline-block;border-radius: 37px;text-decoration: none;padding-top: 10px;padding-bottom: 10px;padding-left: 45px;padding-right: 45px;margin: auto;">
                                            <img src="http://d2bp2kgc0vgu09.cloudfront.net/uploads/general/6035180209cd31614092290.png" style="border: none; float: left; width: 21px; margin-right: 5px; margin-top: 3px;"><span style="float: left; margin-top: 1px;">Confirmed</span>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;float: left;font-size: 16px;color: #606060;font-weight: 700;text-align: center;margin-top: 55px;margin-bottom: 15px;">Follow us</td>
                                </tr>
                                <tr>
                                    <td style="width: auto; margin: auto; display: table;">
                                        <a href="https://www.twitter.com" target="_blank" style="background-color: #B1B1B1; width: 31px; height: 31px; border-radius: 50px; float: left;">
                                            <img src="http://d2bp2kgc0vgu09.cloudfront.net/uploads/general/5ffdd388c17b61610470280.png" style="width: 19px; margin: auto; display: table; padding-top: 7px;" />
                                        </a>
                                        <a href="https://www.facebook.com" target="_blank" style="background-color: #B1B1B1; width: 31px; height: 31px; border-radius: 50px; margin-left: 20px; margin-right: 20px; float: left;">
                                            <img src="http://d2bp2kgc0vgu09.cloudfront.net/uploads/general/5ffdd3a1723841610470305.png" style="width: 9px; margin: auto; display: table; padding-top: 6px;" />
                                        </a>
                                        <a href="https://www.instagram.com/" target="_blank" style="background-color: #B1B1B1; width: 31px; height: 31px; border-radius: 50px; float: right;">
                                            <img src="http://d2bp2kgc0vgu09.cloudfront.net/uploads/general/5ffdd3bc862551610470332.png" style="width: 19px;margin: auto;display: table;padding-top: 6px;">
                                        </a>
                                    </td>
                                </tr>
                                <tr><td height="40"></td></tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
</html>