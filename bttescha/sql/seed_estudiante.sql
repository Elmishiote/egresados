-- Seed SQL para cuentas de prueba (ejecutar con cuidado; preferible usar inserEstudiante.php)
-- EJEMPLO: ajustar hashes y IdUsuario según sea necesario.

-- NOTA: Este SQL usa placeholder para el hash bcrypt. Es mejor usar el script PHP que hace password_hash().

-- Insert de ejemplo (no incluye hash válido):
INSERT INTO Usuario (Nick, CorreoElectronico, Contraseña, tipo_usuario, Borrado)
VALUES ('amy', 'amy@example.local', '$2y$10$PUT_VALID_HASH_HERE', 3, 1);

INSERT INTO Usuario (Nick, CorreoElectronico, Contraseña, tipo_usuario, Borrado)
VALUES ('student', 'student@example.local', '$2y$10$PUT_VALID_HASH_HERE', 3, 1);

-- Luego insertar en estudiante (usar los IdUsuario resultantes):
-- INSERT INTO estudiante (IdUsuario, Matricula, Nombre, PrimerApellido, Estatus_Periodo, DatosLlenos, Borrado)
-- VALUES (<IdUsuarioAmy>, 'amy', 'Amy', 'Ramos', 'TerminoOctavo', 'N', 1);
