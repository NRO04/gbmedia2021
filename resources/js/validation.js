import { required, confirmed, length, email } from "vee-validate/dist/rules";
import { extend } from "vee-validate";

extend("required", {
    ...required,
    message: "Este campo es requerido"
});

extend("email", {
    ...email,
    message: "Escriba un email valido"
});