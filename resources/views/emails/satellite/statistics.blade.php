@component('mail::message')

@component('mail::table')
<table style="background: {{ $mail->color1 }}; padding: 10px 25px">
<tr>
<td>{!! $mail->logo !!}</td>
</tr>
<tr>
<td>{!! $mail->header !!}</td>
</tr>
</table>
@endcomponent

<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td align="center">
                                    <a href="https://gbmediagroup.com" style="background: {{ $mail->color2 }}; padding: 10px; border-radius: 20px;
                                        width: 250px"
                                       class="button"
                                       target="_blank"
                                       rel="noopener">{{
                                    $mail->title1 }}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{!! $mail->section1 !!}

<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td align="center">
                                    <a href="https://gbmediagroup.com" style="background: {{ $mail->color2 }}; padding: 10px; border-radius: 20px;
                                        width: 250px"
                                       class="button"
                                       target="_blank"
                                       rel="noopener">{{
                                    $mail->title2 }}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{!! $mail->section2 !!}

<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td align="center" >
                                    <a href="https://gbmediagroup.com" style="background: {{ $mail->color2 }}; padding: 10px; border-radius: 20px;
                                        width: 250px"
                                       class="button"
                                       target="_blank"
                                       rel="noopener">{{
                                    $mail->title3 }}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{!! $mail->section3 !!}

@component('mail::table')
    <table style="font-size: 13px; background: {{ $mail->color1 }}; padding: 0px 25px">
        <tr>
            <td style="font-size: 13px; line-height: 2px">{!! $mail->sign !!}</td>
            <td style="width: 40px">
                <a href="" target="_blank" >
                    <img src='http://laravel.gbmediagroup.com/public/assets/social_network/facebook.png' style='height:25px'></a>
            </td>
            <td style="width: 40px; line-height: 2px">
                <a  href="" target="_blank" >
                    <img src='http://laravel.gbmediagroup.com/public/assets/social_network/twitter.png' style='height:25px'></a>
            </td>
            <td style="width: 40px; line-height: 2px">
                <a  href="" target="_blank" >
                    <img src='http://laravel.gbmediagroup.com/public/assets/social_network/instagram.png' style='height:25px'></a>
            </td>
            <td style="width: 40px; line-height: 2px">
                <a href="" target="_blank" >
                    <img src='http://laravel.gbmediagroup.com/public/assets/social_network/pinterest.png' style='height:25px'></a>
            </td>
            <td style="width: 40px; line-height: 2px">
                <a href="" target="_blank" >
                    <img src='http://laravel.gbmediagroup.com/public/assets/social_network/linkedin.png' style='height:25px'></a>
            </td>
        </tr>
        <tr>
            <td style="font-size: 13px; line-height: 2px; top: -15px">{!! $mail->url_web !!}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td style="font-size: 13px; line-height: 2px; top: -20px"> Skype: {!! $mail->skype !!}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td style="font-size: 13px; line-height: 2px; top: -25px">Teléfono: {!! $mail->phone !!}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td style="font-size: 13px; line-height: 2px; top: -30px">Celular: {!! $mail->cell !!}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
@endcomponent


@endcomponent
