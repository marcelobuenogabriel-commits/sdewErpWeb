# WebService Module

This module provides helpers to call the Senior SOAP services used by the app.

Configuration (add to your .env):

- SENIOR_CAD_USUARIO_WSDL - WSDL URL for the `cad_usuario` service (default provided)
- SENIOR_USER - user (if required)
- SENIOR_PASSWORD - password (if required)
- SENIOR_TIMEOUT - connection timeout in seconds (default 5)

Usage examples:

- Facade:

    // uses config credentials by default
    SeniorSoap::consultarUsuario(['identifier' => 'cpf-or-id']);

    // override credentials per call (example: use WS-Security)
    SeniorSoap::consultarUsuario(['identifier' => 'cpf-or-id'], ['user' => 'svc-user', 'password' => 'secret', 'auth_type' => 'wsse']);

- Or resolve from container:

    $client = app('senior.soap');
    // default (uses config)
    $client->call('consultarUsuario', ['identifier' => '...']);

    // override credentials per call (HTTP basic)
    $client->call('consultarUsuario', ['identifier' => '...'], ['user' => 'svc-user', 'password' => 'secret', 'auth_type' => 'http']);

Notes:
- The operation names must match the WSDL (adjust `consultarUsuario` wrapper accordingly).
- Supported `auth_type` values: `http` (HTTP basic), `wsse` (WS-Security header), `none` (no auth).
- For production please store credentials in a secret manager and never commit them.
