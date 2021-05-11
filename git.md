# GIT/GITHUB

* Créer le dépôt sur github
* Initialise le dépôt dans le projet
    ```
    git init
    ```
* Ajouter les fichiers à suivre
    ```
    git add -A
    ```
* Vous pouvez taper la commande git status pour voir les fichiers ajoutés au suivi
* Créer le commit
    ```
    git commit -m "first commit"
    ```
* Git peut éventuellement vous demander des informations pour autoriser le commit
    ```
    git config --global user.email addresse_email_du_compte_github
    git config --global user.name pseudo_du_compte_github
    ```
* Changer la branche par défaut de git par celle demandée par github
    ```
    git branch -M main
    ```
* Ajouter l'url du serveur distant sur lequel les modifications vont être envoyées
    ```
    git remote add origin {l'addresse remote indiquée sur votre dépôt github}
    ```
* Pousser les modifications (commit) vers le serveur distant
    ```
    git push -u origin main
    ```