Ce projet utilise Symfony comme framework PHP et PHPMyAdmin pour la gestion de la base de données MySQL. 

Ce README vous guidera à travers les étapes nécessaires pour configurer et démarrer le projet en local.


# Prérequis #

1. Cloner le dépôt
Clonez le dépôt Git du projet sur votre machine locale 

2. Installer les dépendances avec Composer
Une fois que vous avez cloné le projet, vous devez installer les dépendances de Symfony avec Composer. Assurez-vous que Composer est installé sur votre machine.

composer install


# Lancement Docker #

1. choisir dans le fichier my-apache-config.conf la version fonctionelle pour déploiement docker
pas celui pour déploiement Render



2. Configuration de Docker et de Docker Compose
Ce projet utilise Docker et Docker Compose pour configurer un environnement local de développement avec PHP, MySQL et PHPMyAdmin. Pour démarrer les conteneurs Docker, exécutez la commande suivante :

démarrer docker desktop puis 
(supprimer les conteneur existant si conflit)

 # docker-compose up -d #

http://localhost:8090/    port pour l'affichae du site dans le container ( sans bdd )
http://localhost:8899/    port phmyadmin dans le container 
mysql://root:root@localhost:3306   Le serveur de base de données MySQL

3. Création et lien avec la BDD

! bien garder la config present sur my-apache-config.conf qui permet les bonnes redirections 
pour avoir du css en plus du twig intégré dans l'affichage dans mon conteneur 
autrement pas de css

modifier le .env pour connecter vers le phpmyadmin du container 

# DATABASE_URL="mysql://test:pass@db:3306/demo?serverVersion=8.0" #

aller dans le container php sur la machine linux du container : 
# docker exec -it php83 bash #
lancer la création de la base de donné avec le projet symfony : 
# php bin/console doctrine:database:create #
puis php bin/console doctrine:schema:update --force


4. Accéder à l'application
Une fois le serveur démarré, vous pouvez accéder à l'application dans votre navigateur en allant à l'adresse suivante :  http://localhost:8000





pour recréer des migrations à partir de 0
# php bin/console doctrine:query:sql "DROP TABLE IF EXISTS doctrine_migration_versions" #