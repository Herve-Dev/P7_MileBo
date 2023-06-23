# P7_MileBo
Créez un web service exposant une API

![Capture d’écran 2023-06-12 143752](https://github.com/Herve-Dev/P7_MileBo/assets/82519929/ea963771-d12a-4d6b-979f-2766694a6f01)

# Contexte 
BileMo est une entreprise offrant toute une sélection de téléphones mobiles haut de gamme.

Vous êtes en charge du développement de la vitrine de téléphones mobiles de l’entreprise BileMo. Le business modèle de BileMo n’est pas de vendre directement ses produits sur le site web, mais de fournir à toutes les plateformes qui le souhaitent l’accès au catalogue via une API (Application Programming Interface). Il s’agit donc de vente exclusivement en B2B (business to business).

Il va falloir que vous exposiez un certain nombre d’API pour que les applications des autres plateformes web puissent effectuer des opérations.

# Besoin client 
Le premier client a enfin signé un contrat de partenariat avec BileMo ! C’est le branle-bas de combat pour répondre aux besoins de ce premier client qui va permettre de mettre en place l’ensemble des API et de les éprouver tout de suite.

 Après une réunion dense avec le client, il a été identifié un certain nombre d’informations. Il doit être possible de :

- consulter la liste des produits BileMo ;

- consulter les détails d’un produit BileMo ;

- consulter la liste des utilisateurs inscrits liés à un client sur le site web ;

- consulter le détail d’un utilisateur inscrit lié à un client ;

- ajouter un nouvel utilisateur lié à un client ;

- supprimer un utilisateur ajouté par un client.

Seuls les clients référencés peuvent accéder aux API. Les clients de l’API doivent être authentifiés via OAuth ou JWT.

# Installation

1. Faite un git clone du projet

2. Ouvrez votre terminal dans le projet et faite un `composer install`.
   
3. Créez un dossier "jwt" dans le dossier "config" du projet.
   
4. Ouvrez un terminal Git dans le projet et saisissez les commandes suivantes :
   - `openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096`
   - `openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem-pubout`
   - (Une “passphrase” vous sera demandée. Cette passphrase va en quelque sorte servir de clef pour l’encodage/décodage du token. Elle doit rester secrète !)
  
5. Créez votre un fichier .env.local et configurez votre base de données (Vous trouverez un exemple dans le fichier `.env`).
   
6. Dans le fichier `.env.local` a la ligne `JWT_PASSPHRASE=` mettez votre clef précédement créée via OpenSSL.
   
7. Ouvrez un nouveau terminal et taper `php bin/console doctrine:database:create`.
   
8. Taper egalement a la suite `php bin/console doctrine:migrations:migrate`.
   
9. il faut ajouter les datafixtures avec cette ligne a la suite toujours dans le terminal `php bin/console doctrine:fixtures:load --no-interaction`.

10. un derniere ligne dans le terminal `Symfony server:start` pour lancer le serveur.
        
11. Vous êtes encore là ? c'est parfait le projet est installé avec succès !

# route API

```sh
[GET] {/api/smartphones} Route pour récupérer la liste complète des smartphones.
```
```sh
[GET] {/api/smartphone/{id}} Route pour récupérer un smartphone selon son ID.
```
```sh
[DELETE] {/api/smartphone/{id}} Route pour supprimer un smartphone selon son ID.
```
```sh
[POST] {/api/smartphones} Route pour ajouter un smartphone. 
```
```sh
[PUT] {/api/smartphone/{id}} Route pour mettre à jour un smartphone selon son ID.
```
```sh
[GET] {/api/clients} Route pour récupérer la liste des clients (*5).
```
```sh
[GET] {/api/client/{id}} Route pour récupérer un client selon son ID.
```
```sh
[GET] {/api/utilisateurs/client/{id}} Route pour récupérer les utilisateurs d'un client selon son ID.
```
```sh
[GET] {/api/utilisateurs/{id}} Route pour récupérer les détails de l'utilisateur selon son ID. 
```
```sh
[POST] {/api/ajout_utilisateur} Route pour ajouter un utilisateur.
```
```sh
[POST] {/api/delete_utilisateur/{id}} Route pour supprimer un utilisateur.
```
