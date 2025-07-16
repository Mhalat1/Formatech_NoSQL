<?php


-- Table form_contact
CREATE TABLE form_contact (
    id SERIAL PRIMARY KEY,
    prenom VARCHAR(25) NOT NULL,
    nom VARCHAR(25) NOT NULL,
    email VARCHAR(50) NOT NULL,
    nom_institution VARCHAR(50) NOT NULL,
    nom_session VARCHAR(50) NOT NULL,
    nom_module VARCHAR(50) NOT NULL,
    dates VARCHAR(25) NOT NULL,
    offre VARCHAR(25) NOT NULL,
    message TEXT DEFAULT NULL
);

-- Table institution
CREATE TABLE institution (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    telephone BIGINT NOT NULL,
    courriel VARCHAR(255) NOT NULL,
    cree_par_id INT DEFAULT NULL
);

-- Index pour institution
CREATE INDEX idx_nom ON institution (nom);
CREATE INDEX idx_adresse ON institution (adresse);
CREATE INDEX idx_telephone ON institution (telephone);
CREATE INDEX idx_courriel ON institution (courriel);

-- Table invitation
CREATE TABLE invitation (
    id SERIAL PRIMARY KEY,
    institution_id INT NOT NULL,
    invited_by_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(32) NOT NULL,
    expire_le TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    cree_le TIMESTAMP WITHOUT TIME ZONE NOT NULL
);

-- Index pour invitation
CREATE UNIQUE INDEX UNIQ_F11D61A25F37A13B ON invitation (token);
CREATE INDEX IDX_F11D61A210405986 ON invitation (institution_id);
CREATE INDEX IDX_F11D61A2A7B4A7E3 ON invitation (invited_by_id);

-- Table module
CREATE TABLE module (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description VARCHAR(255) NOT NULL,
    commentaire VARCHAR(255) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL
);

-- Index pour module
CREATE INDEX idx_nom_module ON module (nom);
CREATE INDEX idx_description ON module (description);
CREATE INDEX idx_commentaire ON module (commentaire);
CREATE INDEX idx_date_debut_module ON module (date_debut);
CREATE INDEX idx_date_fin_module ON module (date_fin);

-- Table session
CREATE TABLE session (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    type VARCHAR(255) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    description VARCHAR(255) NOT NULL
);

-- Index pour session
CREATE INDEX idx_nom_session ON session (nom);
CREATE INDEX idx_type ON session (type);
CREATE INDEX idx_date_debut ON session (date_debut);
CREATE INDEX idx_date_fin ON session (date_fin);
CREATE INDEX idx_description_session ON session (description);

-- Table session_module
CREATE TABLE session_module (
    id SERIAL PRIMARY KEY,
    module_id INT DEFAULT NULL,
    session_id INT DEFAULT NULL,
    institution_id INT NOT NULL
);

-- Index pour session_module
CREATE INDEX idx_session_id ON session_module (session_id);
CREATE INDEX idx_module_id ON session_module (module_id);
CREATE INDEX idx_institution_id ON session_module (institution_id);

-- Table utilisateur
CREATE TABLE utilisateur (
    id SERIAL PRIMARY KEY,
    institution_id INT DEFAULT NULL,
    prenom VARCHAR(255) NOT NULL,
    nom VARCHAR(255) NOT NULL,
    courriel VARCHAR(255) NOT NULL,
    motdepasse VARCHAR(255) NOT NULL,
    roles JSON NOT NULL,
    commentaire VARCHAR(255) NOT NULL,
    note NUMERIC(5, 2) DEFAULT NULL,
    telephone VARCHAR(20) DEFAULT NULL,
    date_naissance DATE DEFAULT NULL,
    nombre_souscriptions INT NOT NULL,
    date_fin_abonnement TIMESTAMP WITHOUT TIME ZONE DEFAULT NULL,
    nombre_invitations INT DEFAULT 0 NOT NULL,
    date_creation TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    nombre_institutions INT NOT NULL,
    abonnement_type VARCHAR(255) DEFAULT NULL
);

-- Index pour utilisateur
CREATE UNIQUE INDEX UNIQ_1D1C63B344FB41C9 ON utilisateur (courriel);
CREATE INDEX IDX_1D1C63B310405986 ON utilisateur (institution_id);
CREATE INDEX idx_prenom ON utilisateur (prenom);
CREATE INDEX idx_nom_utilisateur ON utilisateur (nom);
CREATE INDEX idx_courriel_utilisateur ON utilisateur (courriel);
CREATE INDEX idx_commentaire_utilisateur ON utilisateur (commentaire);
CREATE INDEX idx_note ON utilisateur (note);
CREATE INDEX idx_telephone ON utilisateur (telephone);
CREATE INDEX idx_date_naissance ON utilisateur (date_naissance);

-- Table utilisateur_institution_session_module
CREATE TABLE utilisateur_institution_session_module (
    id SERIAL PRIMARY KEY,
    utilisateur_id INT DEFAULT NULL,
    session_module_id INT DEFAULT NULL,
    commentaire_module VARCHAR(255) DEFAULT NULL,
    note_module DOUBLE PRECISION DEFAULT NULL
);

-- Index pour utilisateur_institution_session_module
CREATE INDEX idx_commentaire_module ON utilisateur_institution_session_module (commentaire_module);
CREATE INDEX idx_note_module ON utilisateur_institution_session_module (note_module);
CREATE INDEX idx_utilisateur_id ON utilisateur_institution_session_module (utilisateur_id);
CREATE INDEX idx_session_module_id ON utilisateur_institution_session_module (session_module_id);

-- Table jour_horaire
CREATE TABLE jour_horaire (
    id SERIAL PRIMARY KEY,
    institution_session_module_id INT NOT NULL,
    jour VARCHAR(10) DEFAULT NULL,
    date_precise DATE DEFAULT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL
);

-- Index pour jour_horaire
CREATE INDEX IDX_1E0B86D5598A7CD1 ON jour_horaire (institution_session_module_id);

-- Table stripe_payment
CREATE TABLE stripe_payment (
    id SERIAL PRIMARY KEY,
    payment_intent_id VARCHAR(255) NOT NULL,
    amount INT NOT NULL,
    currency VARCHAR(255) NOT NULL,
    status VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL
);

-- Table messenger_messages
CREATE TABLE messenger_messages (
    id BIGSERIAL PRIMARY KEY,
    body TEXT NOT NULL,
    headers TEXT NOT NULL,
    queue_name VARCHAR(190) NOT NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    available_at TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    delivered_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NULL
);

-- Index pour messenger_messages
CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name);
CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at);
CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at);

-- Ajout des clés étrangères
ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A210405986 
    FOREIGN KEY (institution_id) REFERENCES institution (id);

ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2A7B4A7E3 
    FOREIGN KEY (invited_by_id) REFERENCES utilisateur (id) ON DELETE CASCADE;

ALTER TABLE jour_horaire ADD CONSTRAINT FK_1E0B86D5598A7CD1 
    FOREIGN KEY (institution_session_module_id) REFERENCES utilisateur_institution_session_module (id);

ALTER TABLE session_module ADD CONSTRAINT FK_634F2C71AFC2B591 
    FOREIGN KEY (module_id) REFERENCES module (id) ON DELETE CASCADE;

ALTER TABLE session_module ADD CONSTRAINT FK_634F2C71613FECDF 
    FOREIGN KEY (session_id) REFERENCES session (id);

ALTER TABLE session_module ADD CONSTRAINT FK_634F2C7110405986 
    FOREIGN KEY (institution_id) REFERENCES institution (id);

ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B310405986 
    FOREIGN KEY (institution_id) REFERENCES institution (id);

ALTER TABLE utilisateur_institution_session_module ADD CONSTRAINT FK_7E06BF21FB88E14F 
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id);

ALTER TABLE utilisateur_institution_session_module ADD CONSTRAINT FK_7E06BF21EC20F09B 
    FOREIGN KEY (session_module_id) REFERENCES session_module (id);