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

-consulter la liste des produits BileMo ;

-consulter les détails d’un produit BileMo ;

-consulter la liste des utilisateurs inscrits liés à un client sur le site web ;

-consulter le détail d’un utilisateur inscrit lié à un client ;

-ajouter un nouvel utilisateur lié à un client ;

-supprimer un utilisateur ajouté par un client.

Seuls les clients référencés peuvent accéder aux API. Les clients de l’API doivent être authentifiés via OAuth ou JWT.
