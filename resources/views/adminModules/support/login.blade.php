<link href="{{ asset('css/coreuipro.css') }}" rel="stylesheet">
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
<link href="{{ asset('css/gblaravel.css') }}" rel="stylesheet">

<title>Login Soporte</title>

<style>
    body {
        overflow: hidden;
    }
</style>

<div class="b-overlay position-absolute" id="global-spinner" style="top: 0px; left: 0px; bottom: 0px; right: 0px; z-index: 10;">
    <div class="position-absolute bg-light" style="top: 0px; left: 0px; bottom: 0px; right: 0px; opacity: 0.85; backdrop-filter: blur(2px);"></div>
    <div class="position-absolute" style="top: 50%; left: 50%; transform: translateX(-50%) translateY(-50%);"><span aria-hidden="true" class="spinner-border"><!----></span></div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card-body">
            <div class="col-6 d-none">
                <form action="{{ $url }}" id="form-login" method="post">
                    <div class="form-group">
                        <label for="email" class="required">Email</label>
                        <input class="form-control" id="email" name="userid" required type="text" value="{{ $email }}"/>
                    </div>
                    <div class="form-group">
                        <label for="password" class="required">Contraseña</label>
                        <input class="form-control" id="password" name="passwd" required type="password" value="{{ $password }}"/>
                    </div>
                    <input type="hidden" name="__CSRFToken__" value="46b20f865a92a299185686177244062af042714f">
                    <input type="hidden" name="do" value="scplogin">
                    <input type="hidden" name="submit" value="">
                    <button type="submit" id="btn-submit" class="btn btn-success">LOGIN</button>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('vendors/jquery/js/jquery.min.js') }}"></script>
</div>

<script>
    $(document).ready(function () {
        console.log("DO SUBMIT");
        $('#btn-submit').click();
    });
</script>
