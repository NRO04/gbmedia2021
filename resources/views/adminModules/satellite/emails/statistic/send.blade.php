<!DOCTYPE html>
<html>
<style>
    html {
        font-family: sans-serif;
    }
    body {
        margin: 0px;
    }
    .containerHeader, .containerFooter {
    background: {!! $mail['color1'] !!};
    color: #FFF;
        width: 100%;
        margin: 0 auto 25px;
        padding: 10px 0px;
    }
    .containerBody {
        text-align: justify;
    }
    .containerFooter {
        border-top: 3px solid {!! $mail['color2'] !!};
        margin: 0;
    }
    .studioLogo {
        padding: 0 30px;
    }
    .studioLogo img {
        width: 220px;
        padding: 15px 10px;
    }
    .info {
        padding: 0 30px 20px;
    }
    .containerBody .info {
        margin: 0 auto;
    }
    .title {
        text-align: center;
    }
    .containerOne .item {
        padding: 2px 0;
    }
    .item{
        font-weight: normal;
        font-size: 12px;
    }
    .containerOne, .containerTwo, .containerThree {
        margin: 25px 0;
    }
    .areaName {
        font-weight: bold;
    }
    .copyright {
        font-weight: bold;
    }
    .title span {
        background-color: {!! $mail['color2'] !!};
        font-size: 14px;
        color: white;
        padding: 10px 25px;
        border-radius: 20px;
        text-transform: uppercase;
    }
    .containerFooterOne {
        padding: 0 30px;
    }
    .containerFooterOneInnerOne span a {
        color: #FFF;
        text-decoration: underline;
    }
    .containerFooterOneInnerOne {
        float: left;
    }
    .containerFooterOneInnerTwo {
        float: right;
    }
    .socialMediaIcon {
        display: inline;
        margin: 5px;
    }
    .socialMediaIcon a {
        text-decoration: none;
    }
    .studioTitle {
        font-size: 18px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .clear {
        clear: both;
    }
    .socialMediaIcon img {
        width: 28px;
        border-radius: 5px;
    }
</style>
<head>
    <title></title>
    <link rel="stylesheet" href="">
</head>
<body>
        <div class="containerHeader">
            <div class="studioLogo">
                {!! $mail['logo'] !!}
            </div>
            <div class="info">
                {!! $mail['header'] !!}
            </div>
        </div>
        <div class="containerBody">
            <div class="info">
                <div class='title'><span>{!! $mail['title1'] !!}</span></div>
                <div class='containerOne'>{!! $mail['section1'] !!}</div>
                <div class='title'><span>{!! $mail['title2'] !!}</span></div>
                <div class='containerTwo'>{!! $mail['section2'] !!}</div>
                <div class='title'><span>{!! $mail['title3'] !!}</span></div>
                <div class='containerThree'>{!! $mail['section3'] !!}</div>
            </div>
        </div>
        <div class="containerFooter">
            <div class="containerFooterOne">
                <div class="containerFooterOneInnerOne">
                    <div class='item'>
                        <span class='itemInner areaName'>{!! $mail['sign'] !!}</span>
                    </div>
                    <div class='item'>
                        <span class='itemInner webPage'>{!! $mail['url_web'] !!}</span>
                    </div>
                    <div class='item'>
                        <label>Skype:</label>
                        <span class='itemInner skype'>{!! $mail['skype'] !!}</span>
                    </div>
                    <div class='item'>
                        <label>Tel&eacute;fono:</label>
                        <span class='itemInner phone'>{!! $mail['phone'] !!}</span>
                    </div>
                    <div class='item'>
                        <label>Celular:</label>
                        <span class='itemInner cellphone'>{!! $mail['cell'] !!}</span>
                    </div>

                    <div class="item">
                        <span class="itemInner copyright">&copy; 2020 {!! $mail['studio'] !!}.Todos los derechos reservados.</span>
                    </div>
                </div>
                <div class="containerFooterOneInnerTwo">
                    <div class='socialMediaIcon'>
                        <a href='facebook.com/{!! $mail['facebook'] !!}'>
                            <img src='/assets/social_network/facebook.png' alt='Facebook'>
                        </a>
                    </div>
                    <div class='socialMediaIcon'>
                        <a href='twitter.com/{!! $mail['twitter'] !!}'>
                            <img src='/assets/social_network/twitter.png' alt='Facebook'>
                        </a>
                    </div>
                    <div class='socialMediaIcon'>
                        <a href='instagram.com/{!! $mail['instagram'] !!}'>
                            <img src='/assets/social_network/instagram.png' alt='Facebook'>
                        </a>
                    </div>
                    <div class='socialMediaIcon'>
                        <a href='linkedin.com/{!! $mail['linkedin'] !!}'>
                            <img src='/assets/social_network/pinterest.png' alt='Facebook'>
                        </a>
                    </div>
                    <div class='socialMediaIcon'>
                        <a href='pinterest.com/{!! $mail['pinterest'] !!}'>
                            <img src='/assets/social_network/linkedin.png' alt='Facebook'>
                        </a>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
</body>
</html>
