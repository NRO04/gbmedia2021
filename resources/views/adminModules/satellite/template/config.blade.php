@extends("layouts.app")
@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card table-responsive">
			<div class="card-header">
				<div class="row">
					<div class="col-lg-12">
					    <span class="span-title">Configuracion Plantilla </span>
					    <span class="span-title text-danger">{{ $status->name }}</span>
					</div>
	         </div>
			</div>
			<div class="card-body">
				<form action="" method="post" id="form-update" accept-charset="utf-8">
					@csrf
					<div class="form-group row">
						<div class="col-lg-8 pt-1">
							<label>Página</label>
							<select class="form-control" id="page" name="page">
			                    <option value = "null">Seleccione una página...</option>
			                    @foreach ($pages as $key => $page)
			                        <option value="{{ $key }}">{{ $page->name }}</option>
			                    @endforeach
			                </select>
						</div>
					</div>
					<!--fields-->
					<div class="d-none" id="div-fields">
						<input type="hidden" name="template_page_id" id="template_page_id" >
						<div class="form-group row">
							<div class="col-lg-8 pt-1">
								<label>Subject</label>
								<input type="text" class="form-control" name="subject" id="subject"  />
							</div>
						</div>

						<div class="form-group row">
							<div class="col-lg-11">
								<label>Body</label>
								<textarea name="body" id="body_email" class="" ></textarea>
							</div>
						</div>
						<!--switch-->
						<div class="row">
							<div class="col-lg-12">
								<div class="row">

										<div class="form-group row col-lg-6">
											<div class="col-lg-12">
												<div class="row">
													<div class="col-lg-2">
														<label class='c-switch c-switch-pill c-switch-label c-switch-success c-switch-sm mt-2'>
								                            <input type='checkbox' class='c-switch-input' name='nick' id="nick"/>
								                            <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
								                        </label>
													</div>
													<div class="col-lg-10">
														<label class="pt-2">Nick ((nick))  </label>
													</div>
												</div>
						                    </div>
										</div>
										<div class="form-group row col-lg-6">
											<div class="col-lg-12">
												<div class="row">
													<div class="col-lg-2">
														<label class='c-switch c-switch-pill c-switch-label c-switch-success c-switch-sm mt-2'>
								                            <input type='checkbox' class='c-switch-input' name='access' id="access"/>
								                            <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
								                        </label>
													</div>
													<div class="col-lg-10">
														<label class="pt-2">Email ((access))  </label>
													</div>
												</div>
						                    </div>
										</div>

										<div class="form-group row col-lg-6">
											<div class="col-lg-12">
												<div class="row">
													<div class="col-lg-2">
														<label class='c-switch c-switch-pill c-switch-label c-switch-success c-switch-sm mt-2'>
								                            <input type='checkbox' class='c-switch-input' name='full_name' id="full_name"/>
								                            <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
								                        </label>
													</div>
													<div class="col-lg-10">
														<label class="pt-2">Nombre Completo ((full_name))  </label>
													</div>
												</div>
						                    </div>
										</div>
										<div class="form-group row col-lg-6">
											<div class="col-lg-12">
												<div class="row">
													<div class="col-lg-2">
														<label class='c-switch c-switch-pill c-switch-label c-switch-success c-switch-sm mt-2'>
								                            <input type='checkbox' class='c-switch-input' name='password' id="password"/>
								                            <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
								                        </label>
													</div>
													<div class="col-lg-10">
														<label class="pt-2">Clave ((password))  </label>
													</div>
												</div>
						                    </div>
										</div>

								</div>
							</div>
						</div>
						<!--/switch-->

						<div class="form-group row">
							<div class="col-lg-8 pt-1">
								<label>Ultima Modificación</label>
								<span class="text-danger" id="last_modification"></span>
							</div>
						</div>

						<div class="form-group">
							<button type="button" class="btn btn-warning btn-sm float-right mb-2" id="btn-update"><i class="fa fa-edit"></i>Modificar</button>
						</div>
					</div>
					<!--/fields-->
				</form>
			</div>
		</div>
	</div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    tinymce.init({
        selector: "#body_email",
        menubar: false,
        skin: "oxide-dark", content_css: "dark",
        plugins: "emoticons advlist lists paste image media preview code link",
        toolbar: "fontsizeselect | bold italic underline link| numlist bullist | forecolor backcolor |  alignleft aligncenter alignright alignjustify | emoticons | image | code",
        images_upload_handler: function (blobInfo, success, failure,folderName) {
			var xhr, formData;
			xhr = new XMLHttpRequest();
			xhr.withCredentials = false;
			xhr.open('POST', "{{ route('satellite.upload_image') }} ");

			var token = document.head.querySelector('[name=csrf-token]').content;
			xhr.setRequestHeader('X-CSRF-Token', token);

			xhr.onload = function() {
			var json;

			if (xhr.status < 200 || xhr.status >= 300) {
			failure('HTTP Error: ' + xhr.status);
			return;
			}
			console.log(json);
			json = JSON.parse(xhr.responseText);

			if (!json || typeof json.location != 'string') {
			failure('Invalid JSON: ' + xhr.responseText);
			return;
			}
			success(json.location);

			};

			xhr.onerror = function () {
			failure('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
			};

			formData = new FormData();
			formData.append('file', blobInfo.blob(), blobInfo.filename());
			xhr.send(formData);
			},
        height: 450,
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
        advlist_number_styles: "lower-alpha lower-roman upper-alpha upper-roman",
        force_br_newlines: false,
        force_p_newlines: false,
        browser_spellcheck: true,
        contextmenu: false,
        color_map: [
            "#BFEDD2", "Light Green",
            "#FBEEB8", "Light Yellow",
            "#F8CAC6", "Light Red",
            "#ECCAFA", "Light Purple",
            "#C2E0F4", "Light Blue",
            "#2DC26B", "Green",
            "#F1C40F", "Yellow",
            "#E03E2D", "Red",
            "#B96AD9", "Purple",
            "#3598DB", "Blue",
            "#169179", "Dark Turquoise",
            "#E67E23", "Orange",
            "#BA372A", "Dark Red",
            "#843FA1", "Dark Purple",
            "#236FA1", "Dark Blue",
            "#ECF0F1", "Light Gray",
            "#CED4D9", "Medium Gray",
            "#95A5A6", "Gray",
            "#7E8C8D", "Dark Gray",
            "#34495E", "Navy Blue",
            "#000000", "Black",
            "#ffffff", "White",
        ],

        content_style: ".mce-content-body {font-size:14px;font-family:Arial,sans-serif;color: white; background-color: #393a42}",
        image_advtab: true,
        convert_urls: false,
        setup: function (editor) {
            editor.on("change", function () {
                tinymce.triggerSave();
            });

        },
    });

});

$("#btn-update").on("click", function(){
        $("#btn-update").prop('disabled' , false);
        ResetValidations();
        formData = new FormData(document.getElementById('form-update'));
        $.ajax({
            url: '{{ route('satellite.update_config')}}',
            type: 'POST',
            processData: false,
            contentType: false,
            data: formData,
        })
        .done(function(res) {
            if (res.success) {
                 Toast.fire({
                    icon: "success",
                    title: "Se ha modificado la plantilla exitosamente",
                });

            } else {
                Toast.fire({
                    icon: "error",
                    title: "Ha ocurrido un error, comuniquese con el ADMIN",
                });
                $("#btn-update").prop('disabled' , false);
            }
        })
        .fail(function(res) {
            CallBackErrors(res);
            Toast.fire({
                    icon: "error",
                    title: "Verifique la informacion de los campos",
                });
            $("#btn-update").prop('disabled' , false);
        });

    });

$("#page").on("change", function(){
	pages = @json($pages);

	if ($(this).val() != "null") {
		page = pages[$(this).val()];
		console.log(page);

		content = (page.body == null)? "" : page.body;

		$("#template_page_id").val(page.id);
		$("#subject").val(page.subject);
		$("#body_email").html(content);


		tinymce.activeEditor.setContent(content);

		checked = (page.nick == 1)? true : false;
		$("#nick").prop("checked", checked);

		checked = (page.full_name == 1)? true : false;
		$("#full_name").prop("checked", checked);

		checked = (page.access == 1)? true : false;
		$("#access").prop("checked", checked);

		checked = (page.password == 1)? true : false;
		$("#password").prop("checked", checked);

		if (page.updated_at != null){
			formatted_date = moment(page.updated_at).format("DD MMM YYYY");
			$("#last_modification").html(page.first_name+" "+page.last_name+" at "+ formatted_date);
		}
		else{
			$("#last_modification").html("");
		}
		$("#div-fields").removeClass("d-none");

	}
	else{
		$("#div-fields").addClass("d-none");
	}

});
</script>
@endpush
