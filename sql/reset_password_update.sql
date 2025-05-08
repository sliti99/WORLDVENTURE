-- Ajouter les colonnes pour la réinitialisation du mot de passe
ALTER TABLE users
ADD COLUMN reset_token VARCHAR(64) NULL,
ADD COLUMN reset_token_expiry DATETIME NULL; 