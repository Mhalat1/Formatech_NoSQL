#!/usr/bin/env bash
set -e

echo "🚀 Démarrage du conteneur Symfony..."

# Phase de pré-déploiement
if [ "$RENDER" = "true" ]; then
  echo "📦 Installation des dépendances..."
  composer install --no-dev --optimize-autoloader --no-interaction

  echo "🗄️ Migration de la base de données..."
  php bin/console doctrine:migrations:migrate --no-interaction
fi

echo "🌐 Démarrage du serveur web..."
exec php -S 0.0.0.0:8000 -t public