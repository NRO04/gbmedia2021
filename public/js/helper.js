const DEV = false;

$(document).ready(function() {
    $("#alert-success")
        .fadeTo(2000, 500)
        .slideUp(500, function() {
            $("#alert-success").slideUp(500);
        });

    $('[data-toggle="tooltip"]').tooltip();
});

function ResetValidations() {
    $("form")
        .find("input, textarea, select, checkbox")
        .removeClass("is-invalid");
    $(".invalid-feedback").remove();
}

function CallBackErrors(res) {
    if (res.status == 403) {
        SwalGB.fire({
            title: "Upsss...",
            text: "Usted no tiene permiso para esta funcionalidad",
            icon: "danger",
            showCancelButton: false
        }).then(result => {
            document.location.href = "/dashboard";
        });
    } else {
        let errors = res.responseJSON.errors;
        $.each(errors, function(input, errors) {
            $('[name="' + input + '"]').addClass("is-invalid");
            $.each(errors, function(id, error) {
                $('[name="' + input + '"]').after(
                    '<div class="invalid-feedback">' + error + "</div>"
                );
            });
        });
    }

    EnableModalActionButtons();
}

function ResetModalForm(selector) {
    $(selector).trigger("reset");
    $(".modal-footer button").prop("disabled", false);
}

function SplitURL(url, character = "/") {
    let url_splitted = url.split(character);
    return url_splitted;
}

function DisableModalActionButtons() {
    if (!DEV) {
        $(".modal-footer button").prop("disabled", true);
    }
}

function EnableModalActionButtons() {
    $(".modal-footer button").prop("disabled", false);
}

function ChangeTheme() {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        }
    });
    clase = $("#body-id").attr("class");
    $.ajax({
        url: "/user/users/changetheme",
        type: "POST",
        data: { clase: clase }
    }).done(function() {
        console.log("success");
    });
}

$(".only-numbers").keydown(function() {
    key = event.keyCode || event.which;
    specials = [
        8,
        37,
        38,
        64,
        96,
        97,
        98,
        99,
        100,
        101,
        102,
        103,
        104,
        105,
        164
    ];
    key_specials = false;
    for (i = 0; i < specials.length; i++) {
        if (key == specials[i]) {
            key_specials = true;
            break;
        }
    }
    console.log(key_specials);
    keyboard = String.fromCharCode(key);
    letters = "1234567890";

    if (letters.indexOf(keyboard) == -1 && !key_specials) return false;
});

$(".numbers-decimals").keydown(function() {
    key = event.keyCode || event.which;
    specials = "8-37-38-64-164";
    key_specials = false;
    for (var i in specials) {
        if (key == specials[i]) {
            key_specials = true;
            break;
        }
    }

    keyboard = String.fromCharCode(key);
    letters = ".1234567890";

    if (letters.indexOf(keyboard) == -1 && !key_specials) return false;
});

$(".only-letters").keydown(function() {
    key = event.keyCode || event.which;
    specials = "8-37-38-64-164";
    key_specials = false;
    for (var i in specials) {
        if (key == specials[i]) {
            key_specials = true;
            break;
        }
    }

    keyboard = String.fromCharCode(key);
    letters = "abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ";

    if (letters.indexOf(keyboard) === -1 && !key_specials) return false;
});

$(document).ready(function() {
    tinymce.init({
        selector: ".description",
        menubar: false,
        skin: "oxide-dark",
        content_css: "dark",
        plugins: "emoticons advlist lists paste image media preview code link",
        toolbar:
            "fontsizeselect | bold italic underline link| numlist bullist | forecolor backcolor |  alignleft aligncenter alignright alignjustify | emoticons",
        height: 250,
        width: "100%",
        statusbar: false,
        fontsize_formats: "11px 12px 14px 16px 18px 24px 36px 48px",
        paste_as_text: true,
        paste_use_dialog: false,
        paste_auto_cleanup_on_paste: true,
        paste_convert_headers_to_strong: false,
        paste_strip_class_attributes: "all",
        paste_remove_spans: true,
        paste_remove_styles: true,
        advlist_bullet_styles: "square",
        advlist_number_styles:
            "lower-alpha lower-roman upper-alpha upper-roman",
        force_br_newlines: false,
        force_p_newlines: false,
        browser_spellcheck: true,
        contextmenu: false,
        color_map: [
            "#BFEDD2",
            "Light Green",
            "#FBEEB8",
            "Light Yellow",
            "#F8CAC6",
            "Light Red",
            "#ECCAFA",
            "Light Purple",
            "#C2E0F4",
            "Light Blue",
            "#2DC26B",
            "Green",
            "#F1C40F",
            "Yellow",
            "#E03E2D",
            "Red",
            "#B96AD9",
            "Purple",
            "#3598DB",
            "Blue",
            "#169179",
            "Dark Turquoise",
            "#E67E23",
            "Orange",
            "#BA372A",
            "Dark Red",
            "#843FA1",
            "Dark Purple",
            "#236FA1",
            "Dark Blue",
            "#ECF0F1",
            "Light Gray",
            "#CED4D9",
            "Medium Gray",
            "#95A5A6",
            "Gray",
            "#7E8C8D",
            "Dark Gray",
            "#34495E",
            "Navy Blue",
            "#000000",
            "Black",
            "#ffffff",
            "White"
        ],

        content_style:
            ".mce-content-body {font-size:14px;font-family:Arial,sans-serif;color: white; background-color: #393a42}",
        image_advtab: true,
        convert_urls: false,
        setup: function(editor) {
            editor.on("change", function() {
                tinymce.triggerSave();
            });
        }
    });
});

$("#inputfile").change(function() {
    if (this.files.length != 0) $("#spanFile").html("Archivos Cargados");
    else $("#spanFile").html("Seleccionar Archivos");
});

function gbPulsing(type) {
    var a = document.getElementsByClassName("pulsing-active");
    visible = type == 1 ? "visible" : "hidden";
    color = type == 1 ? "#f71a1a" : "#e1e1e1";
    {
        for (var i = 0; i < a.length; i++) {
            a[i].style.visibility = visible;
            a[i].style.color = color;
        }
    }
    type = type == 1 ? 0 : 1;
    setTimeout("gbPulsing(" + type + ")", 500);
}

function removePulsing(id) {
    $("#" + id).css("visibility", "visible");
    $("#" + id).removeClass("pulsing-active");
    $("#" + id).css("color", "#3D3A3A");
}

function zoomImageGB() {
    var $gallery = new SimpleLightbox(".gallery a", {});
}

function collapseMenu() {
    $("#sidebar").removeClass(
        "c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show"
    );
    $("#sidebar").addClass(
        "c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show c-sidebar-unfoldable"
    );
}

function removePulsingFromMenuIcon(element_id) {
    $(element_id).css("visibility", "initial");
    $(element_id).css("color", "#8a93a2");
    $(element_id).removeClass("pulsing-active");
}

window.onload = gbPulsing(1);
