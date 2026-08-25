<?php
namespace Deployer;

require 'recipe/symfony.php';

// ---------------------------------------------------------------------------
// Général
// ---------------------------------------------------------------------------

// Nombre de releases conservées (rollback possible sur les 3 dernières)
set('keep_releases', 3);

// Nom du projet
set('application', 'demo.amelayes-biophp.net');

// Dépôt Git
set('repository', 'https://github.com/amelaye/biophp-demo.git');

// ---------------------------------------------------------------------------
// Permissions / sécurité
// ---------------------------------------------------------------------------

// Le serveur web réel est www-data (pool php-fpm), pas "deploy".
// C'est lui qui doit pouvoir écrire dans var/.
set('http_user', 'www-data');

// "acl" au lieu de "chown" — pose des ACL ciblées pour www-data
// sans changer le propriétaire ni ouvrir les droits à tout le monde.
// (Nécessite le paquet "acl" installé sur le serveur.)
set('writable_mode', 'acl');

// Sudo nécessaire pour poser les ACL pendant le déploiement.
set('writable_use_sudo', true);

set('ssh_multiplexing', true);

set('bin/php', '/usr/bin/php8.1');
set('bin/composer', '/usr/bin/php8.1 /usr/local/bin/composer');


// ---------------------------------------------------------------------------
// Fichiers / dossiers partagés entre les releases
// ---------------------------------------------------------------------------

// Fichiers partagés : le .env vit dans shared/ et est symliké dans chaque release.
add('shared_files', ['.env']);

// Les logs et sessions doivent PERSISTER entre les déploiements.
add('shared_dirs', [
    'var/log',
    'var/sessions',
]);

// ---------------------------------------------------------------------------
// Dossiers inscriptibles par le serveur web
// ---------------------------------------------------------------------------

add('writable_dirs', [
    'var/cache',
    'var/log',
    'var/sessions',
]);

// ---------------------------------------------------------------------------
// Hôtes
// ---------------------------------------------------------------------------

host('biophp-demo-prod')
    ->set('deploy_path', '/home/web/{{application}}')
    ->set('branch', 'master')
    ->setLabels(['stage' => 'prod']);

// ---------------------------------------------------------------------------
// Tâches
// ---------------------------------------------------------------------------

// On NE redéfinit PAS la tâche 'deploy' ici. recipe/symfony.php le
// fait déjà (deploy:prepare -> deploy:vendors -> deploy:cache:clear ->
// deploy:publish).

// Réchauffe le cache prod en tant que www-data (bonnes permissions)
task('deploy:cache_warmup', function () {
    run('cd {{release_path}} && {{bin/php}} bin/console cache:warmup --env=prod');
});

after('deploy:symlink', 'deploy:cache_warmup');

// Si le déploiement échoue, on déverrouille automatiquement.
after('deploy:failed', 'deploy:unlock');

// Migration de la base avant de basculer le symlink sur la nouvelle release.
before('deploy:symlink', 'database:migrate');
