# Cambios realizados por Codex

## 2026-06-06

- Se elimino el componente `<x-passkey-verify />` de `resources/views/pages/auth/login.blade.php`.
- Con ese cambio, el login del aplicativo deja de mostrar el boton "Sign in with a passkey".
- Tambien desaparece el separador "Or continue with email", porque ese texto pertenecia al mismo componente de passkey.
- El cambio quedo limitado a la pantalla de login; no se modifico el componente reusable de passkey para evitar afectar otros flujos donde todavia podria usarse.
