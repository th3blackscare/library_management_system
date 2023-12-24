composer install
composer run-script post-root-package-install
composer run-script post-create-project-cmd
composer run-script seed-books-to-db
composer run-script seed-borrowers-to-db
