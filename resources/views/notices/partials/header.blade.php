@php

    /*
    |--------------------------------------------------------------------------
    | NOTICE HEADER IMAGES
    |--------------------------------------------------------------------------
    |
    | Put these files inside:
    |
    | retention-monitoring/public/images/
    |
    | Required files:
    |
    |   cdk-logo.png
    |   ite-logo.png
    |   facebook.png
    |   email.png
    |
    | Images are converted to Base64 because this is more reliable
    | when generating PDFs using DomPDF.
    |
    |--------------------------------------------------------------------------
    */


    $imagePath = public_path('images');


    /*
    |--------------------------------------------------------------------------
    | IMAGE PATHS
    |--------------------------------------------------------------------------
    */

    $cdkLogoPath =
        $imagePath .
        DIRECTORY_SEPARATOR .
        'cdk-logo.png';

    $iteLogoPath =
        $imagePath .
        DIRECTORY_SEPARATOR .
        'ite-logo.png';

    $facebookIconPath =
        $imagePath .
        DIRECTORY_SEPARATOR .
        'facebook.png';

    $emailIconPath =
        $imagePath .
        DIRECTORY_SEPARATOR .
        'email.png';


    /*
    |--------------------------------------------------------------------------
    | IMAGE TO BASE64 FUNCTION
    |--------------------------------------------------------------------------
    */

    if (!function_exists('noticeImageBase64')) {

        function noticeImageBase64($path)
        {

            if (!file_exists($path)) {
                return null;
            }


            $extension = strtolower(
                pathinfo($path, PATHINFO_EXTENSION)
            );


            switch ($extension) {

                case 'png':

                    $mime = 'image/png';

                    break;


                case 'jpg':
                case 'jpeg':

                    $mime = 'image/jpeg';

                    break;


                case 'gif':

                    $mime = 'image/gif';

                    break;


                case 'webp':

                    $mime = 'image/webp';

                    break;


                default:

                    return null;

            }


            $imageData = file_get_contents($path);


            if ($imageData === false) {
                return null;
            }


            return 'data:' .
                $mime .
                ';base64,' .
                base64_encode($imageData);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | CONVERT HEADER IMAGES
    |--------------------------------------------------------------------------
    */

    $cdkLogo =
        noticeImageBase64($cdkLogoPath);

    $iteLogo =
        noticeImageBase64($iteLogoPath);

    $facebookIcon =
        noticeImageBase64($facebookIconPath);

    $emailIcon =
        noticeImageBase64($emailIconPath);

@endphp



<style>

/*
|--------------------------------------------------------------------------
| HEADER WRAPPER
|--------------------------------------------------------------------------
|
| 1-INCH MARGINS:
|
| Top    = 1 inch
| Right  = 1 inch
| Bottom = 0
| Left   = 1 inch
|
| This keeps the complete header inside the document's
| left, right, and top margins.
|
|--------------------------------------------------------------------------
*/

.notice-header-wrapper {

    margin-top: 1in;

    margin-right: 1in;

    margin-left: 1in;

    margin-bottom: 0;

    width: auto;

    padding: 0;

}



/*
|--------------------------------------------------------------------------
| MAIN NOTICE HEADER
|--------------------------------------------------------------------------
*/

.notice-header {

    width: 100%;

    border-collapse: collapse;

    border-spacing: 0;

    margin: 0;

    padding: 0;

}



/*
|--------------------------------------------------------------------------
| HEADER ROW
|--------------------------------------------------------------------------
*/

.notice-header tr {

    margin: 0;

    padding: 0;

}



/*
|--------------------------------------------------------------------------
| LEFT LOGO COLUMN
|--------------------------------------------------------------------------
*/

.notice-header-left {

    width: 14%;

    text-align: center;

    vertical-align: middle;

    padding: 0;

    margin: 0;

}



/*
|--------------------------------------------------------------------------
| CENTER INFORMATION COLUMN
|--------------------------------------------------------------------------
*/

.notice-header-center {

    width: 72%;

    text-align: center;

    vertical-align: middle;

    padding: 0;

    margin: 0;

}



/*
|--------------------------------------------------------------------------
| RIGHT LOGO COLUMN
|--------------------------------------------------------------------------
*/

.notice-header-right {

    width: 14%;

    text-align: center;

    vertical-align: middle;

    padding: 0;

    margin: 0;

}



/*
|--------------------------------------------------------------------------
| CDK LOGO
|--------------------------------------------------------------------------
*/

.notice-cdk-logo {

    width: 88px;

    height: 88px;

    display: block;

    margin: 0 auto;

}



/*
|--------------------------------------------------------------------------
| ITE LOGO
|--------------------------------------------------------------------------
*/

.notice-ite-logo {

    width: 88px;

    height: 88px;

    display: block;

    margin: 0 auto;

}



/*
|--------------------------------------------------------------------------
| SCHOOL NAME
|--------------------------------------------------------------------------
*/

.notice-school-name {

    font-family: Arial,
                 Helvetica,
                 sans-serif;

    font-size: 15px;

    font-weight: bold;

    text-transform: uppercase;

    line-height: 1;

    margin: 0;

    padding: 0;

    white-space: nowrap;

}



/*
|--------------------------------------------------------------------------
| SCHOOL ADDRESS
|--------------------------------------------------------------------------
*/

.notice-school-address {

    font-family: Arial,
                 Helvetica,
                 sans-serif;

    font-size: 11px;

    font-weight: normal;

    line-height: 1;

    margin: 3px 0 0 0;

    padding: 0;

    white-space: nowrap;

}



/*
|--------------------------------------------------------------------------
| DEPARTMENT NAME
|--------------------------------------------------------------------------
*/

.notice-department {

    font-family: Arial,
                 Helvetica,
                 sans-serif;

    font-size: 14px;

    font-weight: bold;

    text-transform: uppercase;

    line-height: 1;

    margin: 4px 0 0 0;

    padding: 0;

    white-space: nowrap;

}



/*
|--------------------------------------------------------------------------
| FACEBOOK / OFFICIAL LINK
|--------------------------------------------------------------------------
*/

.notice-official-link {

    font-family: Arial,
                 Helvetica,
                 sans-serif;

    font-size: 11px;

    font-weight: normal;

    line-height: 1;

    margin: 4px 0 0 0;

    padding: 0;

    white-space: nowrap;

}



/*
|--------------------------------------------------------------------------
| EMAIL
|--------------------------------------------------------------------------
*/

.notice-official-email {

    font-family: Arial,
                 Helvetica,
                 sans-serif;

    font-size: 11px;

    font-weight: normal;

    line-height: 1;

    margin: 4px 0 0 0;

    padding: 0;

    white-space: nowrap;

}



/*
|--------------------------------------------------------------------------
| CONTACT ICONS
|--------------------------------------------------------------------------
*/

.notice-contact-icon {

    width: 13px;

    height: 13px;

    vertical-align: middle;

    margin-right: 3px;

}



/*
|--------------------------------------------------------------------------
| TAGLINE
|--------------------------------------------------------------------------
*/

.notice-tagline {

    width: 100%;

    text-align: center;

    font-family: Arial,
                 Helvetica,
                 sans-serif;

    font-size: 11px;

    font-style: italic;

    line-height: 1;

    margin: 8px 0 5px 0;

    padding: 0;

}



/*
|--------------------------------------------------------------------------
| DOUBLE HEADER LINE
|--------------------------------------------------------------------------
*/

.notice-divider {

    width: 100%;

    height: 5px;

    border-top: 1px solid #000;

    border-bottom: 2px solid #000;

    margin: 0;

    padding: 0;

}

</style>



<!-- =========================================================
     HEADER WITH 1-INCH TOP, LEFT, AND RIGHT MARGINS
========================================================= -->

<div class="notice-header-wrapper">


    <!-- =========================================================
         NOTICE HEADER
    ========================================================== -->

    <table class="notice-header">

        <tr>


            <!-- =================================================
                 LEFT CDK LOGO
            ================================================== -->

            <td class="notice-header-left">

                @if($cdkLogo)

                    <img
                        src="{{ $cdkLogo }}"
                        class="notice-cdk-logo"
                        alt="Colegio de Kidapawan Logo"
                    >

                @endif

            </td>



            <!-- =================================================
                 CENTER SCHOOL INFORMATION
            ================================================== -->

            <td class="notice-header-center">


                <!-- =============================================
                     SCHOOL NAME
                ============================================== -->

                <div class="notice-school-name">

                    COLEGIO DE KIDAPAWAN, INC.

                </div>



                <!-- =============================================
                     SCHOOL ADDRESS
                ============================================== -->

                <div class="notice-school-address">

                    Quezon Boulevard, Kidapawan City

                </div>



                <!-- =============================================
                     DEPARTMENT
                ============================================== -->

                <div class="notice-department">

                    INFORMATION TECHNOLOGY EDUCATION DEPARTMENT

                </div>



                <!-- =============================================
                     FACEBOOK / OFFICIAL INFORMATION
                ============================================== -->

                <div class="notice-official-link">

                    @if($facebookIcon)

                        <img
                            src="{{ $facebookIcon }}"
                            class="notice-contact-icon"
                            alt="Facebook"
                        >


                    @endif

                    <u>
                        Colegio de Kidapawan – BS in Information Technology
                    </u>

                </div>



                <!-- =============================================
                     EMAIL
                ============================================== -->

                <div class="notice-official-email">

                    @if($emailIcon)

                        <img
                            src="{{ $emailIcon }}"
                            class="notice-contact-icon"
                            alt="Email"
                        >

                    @endif

                    <u>
                        itedepartment@cdk.edu.ph
                    </u>

                </div>


            </td>



            <!-- =================================================
                 RIGHT ITE LOGO
            ================================================== -->

            <td class="notice-header-right">

                @if($iteLogo)

                    <img
                        src="{{ $iteLogo }}"
                        class="notice-ite-logo"
                        alt="ITE Logo"
                    >

                @endif

            </td>


        </tr>

    </table>



    <!-- =========================================================
         TAGLINE
    ========================================================== -->

    <div class="notice-tagline">

        Where quality and relevant education are within everyone’s reach.

    </div>



    <!-- =========================================================
         DOUBLE HEADER DIVIDER
    ========================================================== -->

    <div class="notice-divider"></div>


</div>