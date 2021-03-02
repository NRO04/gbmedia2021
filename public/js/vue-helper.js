export const DEV = false;

export function VUE_ResetValidations()
{
    $(".is-invalid").removeClass("is-invalid");
    $('.invalid-feedback').remove();

    VUE_EnableModalActionButtons();
}

export function VUE_CallBackErrors(res)
{
    if (res.status == 403)
    {
        SwalGB.fire({
            title: "Upsss...",
            text: "Usted no tiene permiso para esta funcionalidad",
            icon: "danger",
            showCancelButton: false,
        }).then(result => {
            document.location.href= "/dashboard" ;
        });
    }
    else
    {
        let errors = res.data.errors;
        $.each(errors, function (input, errors) {
            $('[name="' + input + '"]').addClass('is-invalid');
            $.each(errors, function (id, error) {
                $('[name="' + input + '"]').after('<div class="invalid-feedback">' + error + '</div>');
            });
        });
    }

    VUE_EnableModalActionButtons();
}

export function VUE_ResetModalForm(selector)
{
    $(selector).trigger("reset");
    $('.modal-footer button').prop('disabled', false);
}

export function VUE_SplitURL(url, character = '/')
{
    let url_splitted = url.split(character);
    return url_splitted;
}

export function VUE_DisableModalActionButtons()
{
    if (!DEV) {
        $(".modal-footer button").prop("disabled", true);
    }
}

export function VUE_EnableModalActionButtons()
{
    $(".modal-footer button").prop("disabled", false);
}

export function VUE_removePulsingFromMenuIcon(element_id) {
    $(element_id).css('visibility', 'initial');
    $(element_id).css('color', '#8a93a2');
    $(element_id).removeClass('pulsing-active');
}
