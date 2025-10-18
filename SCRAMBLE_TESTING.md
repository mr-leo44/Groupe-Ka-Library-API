# 🧪 Testing API with Laravel Scramble

## Accéder à la documentation

1. Démarrez le serveur :
```bash
php artisan serve
```

2. Ouvrez votre navigateur :
```
http://localhost:8000/docs/api
```

## 🔐 Tester les endpoints protégés

### Étape 1 : S'authentifier

1. **Trouvez l'endpoint Login** dans la section "Authentication"
2. **Cliquez sur "Try it out"**
3. **Entrez les credentials de test** :
```json
{
  "email": "admin@groupeka.com",
  "password": "Admin@123!"
}
```
4. **Cliquez sur "Execute"**
5. **Copiez le token** depuis la réponse :
```json
{
  "data": {
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz..."
  }
}
```

### Étape 2 : Autoriser Scramble

1. **Cliquez sur le bouton "Authorize"** (🔓) en haut à droite
2. **Collez votre token** dans le champ "Value"
3. **Format** : `Bearer YOUR_TOKEN_HERE` (Scramble ajoute automatiquement "Bearer ")
4. **Cliquez sur "Authorize"**
5. **Le cadenas devient fermé** (🔒) = vous êtes authentifié

### Étape 3 : Tester les endpoints protégés

Maintenant tous les endpoints avec 🔒 utiliseront automatiquement votre token !

**Exemples** :
- `GET /api/user/profile` - Voir votre profil
- `GET /api/user/sessions` - Voir vos sessions actives
- `GET /api/admin/users` - Liste des utilisateurs (admin seulement)

## 📋 Liste des credentials de test

| Rôle | Email | Password | Use Case |
|------|-------|----------|----------|
| Admin | admin@groupeka.com | Admin@123! | Tester endpoints admin |
| Manager | manager@groupeka.com | Manager@123! | Tester rôle manager |
| Member | john.doe@example.com | Member@123! | Tester utilisateur normal |
| Unverified | unverified@example.com | Member@123! | Tester email non vérifié |

## 🎯 Scénarios de test

### Scénario 1 : Parcours utilisateur complet

1. **Register** → Obtenir un token
2. **Get Profile** → Voir ses informations
3. **Update Profile** → Modifier son nom
4. **Change Password** → Changer son mot de passe
5. **Get Sessions** → Voir ses connexions actives
6. **Logout** → Se déconnecter

### Scénario 2 : Gestion des sessions

1. **Login 3 fois** avec device_name différents:
   - `"device_name": "mobile-app"`
   - `"device_name": "tablet"`
   - `"device_name": "desktop"`
2. **Get Sessions** → Voir 3 sessions
3. **Revoke Session** → Supprimer "tablet"
4. **Get Sessions** → Voir 2 sessions
5. **Logout All Devices** → Tout supprimer

### Scénario 3 : Administration (avec admin@groupeka.com)

1. **Login** en tant qu'admin
2. **Get Users** → Voir tous les utilisateurs
3. **Get User Details** → Détails d'un utilisateur spécifique
4. **Update User Role** → Promouvoir un membre en manager
5. **Get Activity Logs** → Voir l'historique des actions
6. **Get Statistics** → Voir les stats du dashboard

### Scénario 4 : Password Reset

1. **Forgot Password** → Demander un reset
   ```json
   {
     "email": "john.doe@example.com"
   }
   ```
2. **Vérifier votre email** (ou Mailtrap en dev)
3. **Copier le token** depuis l'email
4. **Reset Password** → Réinitialiser avec le token
   ```json
   {
     "token": "token-from-email",
     "email": "john.doe@example.com",
     "password": "NewSecure123!@",
     "password_confirmation": "NewSecure123!@"
   }
   ```
5. **Login** avec le nouveau mot de passe

### Scénario 5 : Tester les erreurs

1. **Login avec mauvais password** → 401 Unauthorized
2. **Accéder à /user/profile sans token** → 401 Unauthenticated
3. **Accéder à /admin/users en tant que member** → 403 Forbidden
4. **Register avec email déjà utilisé** → 422 Validation Error
5. **Change password avec mauvais current_password** → 400 Bad Request

## 🔍 Vérifier les annotations @authenticated

Tous ces endpoints doivent afficher un **cadenas fermé 🔒** dans Scramble :

**User Profile** :
- ✅ GET /api/user/profile
- ✅ PUT /api/user/profile
- ✅ POST /api/user/change-password
- ✅ GET /api/user/sessions
- ✅ DELETE /api/user/sessions/{id}

**Auth** :
- ✅ POST /api/auth/logout
- ✅ POST /api/auth/logout-all
- ✅ POST /api/auth/email/resend
- ✅ GET /api/auth/email/verify/{id}/{hash}

**Admin** :
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

**Endpoints publics (pas de 🔒)** :
- POST /api/auth/register
- POST /api/auth/login
- POST /api/auth/social
- POST /api/auth/forgot-password
- POST /api/auth/reset-password

## 💡 Tips & Tricks

### Raccourcis Scramble

- **Ctrl/Cmd + K** : Recherche rapide d'endpoint
- **Cliquer sur "Authorize"** : Configurer le token globalement
- **"Try it out"** : Mode test pour un endpoint
- **"Execute"** : Lancer la requête

### Debugging

Si un endpoint ne fonctionne pas :

1. **Vérifiez le token** dans l'onglet "Headers" de la réponse
2. **Regardez le status code** :
   - 401 = Token manquant/invalide
   - 403 = Pas les permissions nécessaires
   - 422 = Erreur de validation
3. **Consultez les logs** :
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Tester avec curl (alternative)

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@groupeka.com","password":"Admin@123!"}' \
  | jq -r '.data.token')

# 2. Utiliser le token
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/user/profile
```

## 🎨 Personnalisation de Scramble

### Changer le thème

Ajoutez dans `config/scramble.php` :

```php
'ui' => [
    'theme' => 'dark', // ou 'light'
],
```

### Ajouter un logo

```php
'extensions' => [
    'x-logo' => [
        'url' => asset('images/logo.png'),
        'altText' => 'Groupe Ka Library',
    ],
],
```

### Masquer des endpoints

```php
// Dans le controller
/**
 * @hideFromAPIDocumentation
 */
public function internalMethod()
{
    // Ce endpoint n'apparaîtra pas dans Scramble
}
```

## 📊 Statut des endpoints documentés

### ✅ Complètement documentés
- [x] Authentication (register, login, logout, social)
- [x] Password Reset (forgot, reset)
- [x] Email Verification (verify, resend)
- [x] User Profile (get, update, change-password)
- [x] Sessions (list, revoke)
- [x] Admin Users (list, show, update role, delete, restore)
- [x] Admin Audits (list, user audits)
- [x] Admin Activities (list, user activities, security events)
- [x] Admin Statistics

### 🚧 À documenter (future)
- [ ] Books (list, show, purchase)
- [ ] Categories
- [ ] Promotions
- [ ] Reviews

## 🔧 Troubleshooting

### Le cadenas 🔒 n'apparaît pas

**Solution** : Vérifiez que vous avez ajouté `@authenticated` dans le PHPDoc :
```php
/**
 * @authenticated
 */
public function myProtectedMethod()
```

### Scramble ne détecte pas mes routes

**Solution** : Vérifiez que vos routes sont dans `routes/api.php` et ont le bon format :
```php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user/profile', [UserController::class, 'profile']);
});
```

### Les descriptions n'apparaissent pas

**Solution** : Ajoutez des `@property` dans le FormRequest :
```php
/**
 * @property string $email User's email address
 * @property string $password User's password
 */
class LoginRequest extends FormRequest
```

### Le token ne fonctionne pas

**Solutions** :
1. Vérifiez que le token est valide (pas expiré)
2. Vérifiez `SANCTUM_EXPIRATION` dans `.env`
3. Reconnectez-vous pour obtenir un nouveau token
4. Vérifiez que le middleware `auth:sanctum` est appliqué

## 🎓 Ressources

- [Documentation Scramble](https://scramble.dedoc.co/)
- [OpenAPI Specification](https://swagger.io/specification/)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [PHPDoc Tags](https://docs.phpdoc.org/guide/references/phpdoc/tags/index.html)