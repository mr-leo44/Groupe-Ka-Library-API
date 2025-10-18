# 🔐 Fix Scramble - Authentification Bearer Token

## Problème identifié

Les endpoints protégés n'affichaient pas l'icône de cadenas 🔒 dans Scramble UI, ce qui empêchait de tester facilement les routes authentifiées.

## Solutions appliquées

### 1. ✅ Ajout de l'annotation `@authenticated` dans tous les controllers

**Fichiers modifiés** :
- `app/Http/Controllers/Api/UserController.php`
- `app/Http/Controllers/Api/AuditController.php`
- `app/Http/Controllers/Api/EmailVerificationController.php`

**Exemple** :
```php
/**
 * Get authenticated user profile
 * 
 * @authenticated
 * 
 * @response {
 *   "success": true,
 *   "data": {...}
 * }
 */
public function profile(Request $request)
{
    // ...
}
```

### 2. ✅ Amélioration des PHPDoc avec exemples de réponses

**Ajouté** :
- Descriptions détaillées pour chaque endpoint
- Exemples de réponses JSON (`@response`)
- Codes d'erreur documentés (400, 401, 403, 404)
- Tags `@tags` pour regrouper les endpoints

### 3. ✅ Configuration Scramble optimisée

**Fichier** : `config/scramble.php`

**Ajouté** :
- Security scheme Sanctum configuré correctement
- Tags organisés par fonctionnalité
- Headers et format Bearer token

### 4. ✅ Création du ScrambleServiceProvider

**Fichier** : `app/Providers/ScrambleServiceProvider.php`

**But** : Enregistrer automatiquement le security scheme Bearer token dans l'OpenAPI.

### 5. ✅ Guide de test complet

**Fichier** : `SCRAMBLE_TESTING.md`

**Contenu** :
- Instructions pour s'authentifier dans Scramble UI
- Scénarios de test complets
- Credentials de test
- Troubleshooting

## Résultat attendu

### Avant ❌
```
GET /api/user/profile
(Pas d'indication d'authentification requise)
```

### Après ✅
```
🔒 GET /api/user/profile
(Icône de cadenas visible + bouton "Authorize" fonctionnel)
```

## Comment tester

1. **Démarrer le serveur** :
```bash
php artisan serve
```

2. **Ouvrir Scramble** :
```
http://localhost:8000/docs/api
```

3. **Login** :
   - Trouver `POST /api/auth/login`
   - Cliquer "Try it out"
   - Entrer :
     ```json
     {
       "email": "admin@groupeka.com",
       "password": "Admin@123!"
     }
     ```
   - Copier le token

4. **Autoriser** :
   - Cliquer sur "Authorize" (🔓 en haut)
   - Coller le token
   - Cliquer "Authorize"
   - Le cadenas devient fermé 🔒

5. **Tester un endpoint protégé** :
   - Aller sur `GET /api/user/profile`
   - Voir l'icône 🔒
   - Cliquer "Try it out" puis "Execute"
   - Voir la réponse avec vos données

## Endpoints avec authentification 🔒

### User Profile
- ✅ GET /api/user/profile
- ✅ PUT /api/user/profile
- ✅ POST /api/user/change-password
- ✅ GET /api/user/sessions
- ✅ DELETE /api/user/sessions/{id}

### Auth
- ✅ POST /api/auth/logout
- ✅ POST /api/auth/logout-all
- ✅ POST /api/auth/email/resend
- ✅ GET /api/auth/email/verify/{id}/{hash}

### Admin (nécessite rôle admin)
- ✅ GET /api/admin/users
- ✅ GET /api/admin/users/{user}
- ✅ PUT /api/admin/users/{user}/role
- ✅ DELETE /api/admin/users/{user}
- ✅ POST /api/admin/users/{id}/restore
- ✅ GET /api/admin/audits
- ✅ GET /api/admin/audits/user/{user}
- ✅ GET /api/admin/activities
- ✅ GET /api/admin/activities/user/{user}
- ✅ GET /api/admin/activities/security
- ✅ GET /api/admin/statistics

### Endpoints publics (pas d'authentification)
- POST /api/auth/register
- POST /api/auth/login
- POST /api/auth/social
- POST /api/auth/forgot-password
- POST /api/auth/reset-password

## Commandes utiles

```bash
# Régénérer la documentation
php artisan optimize:clear

# Vider le cache Scramble
php artisan cache:clear

# Voir les routes
php artisan route:list

# Tester l'API avec curl
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/user/profile
```

## Troubleshooting

### Le cadenas n'apparaît pas
- Vérifier que `@authenticated` est présent dans le PHPDoc
- Vider le cache : `php artisan cache:clear`
- Redémarrer le serveur

### Token invalide
- Le token expire après 60 minutes (configurable dans `.env`)
- Se reconnecter pour obtenir un nouveau token

### Erreur 401 Unauthenticated
- Vérifier que le token est copié entièrement
- Vérifier que le format est correct (Bearer + token)
- S'assurer que le token n'est pas expiré

## Prochaines étapes

- [ ] Ajouter documentation pour les endpoints Books (quand implémentés)
- [ ] Ajouter exemples de requêtes/réponses pour tous les endpoints
- [ ] Configurer des environnements (dev, staging, prod) dans Scramble
- [ ] Ajouter des webhooks si nécessaire