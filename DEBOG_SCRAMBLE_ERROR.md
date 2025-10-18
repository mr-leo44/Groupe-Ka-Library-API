# 🐛 Debug Scramble preg_match Error

## Erreur rencontrée

```
preg_match(): Argument #2 ($subject) must be of type string, array given 
at vendor/laravel/framework/src/Illuminate/Routing/UrlGenerator.php:674
```

## Cause

Laravel UrlGenerator reçoit un array au lieu d'un string pour un paramètre de route. Cela arrive quand :
1. Un paramètre de route est mal typé
2. Scramble essaie de générer une URL avec un paramètre incorrect
3. Le cache des routes contient une définition invalide

## Solutions appliquées

### 1. ✅ Typage strict des paramètres

**Avant (problématique)** :
```php
public function revokeSession(Request $request, $tokenId)
public function restore($userId)
```

**Après (corrigé)** :
```php
public function revokeSession(Request $request, string $tokenId)
public function restore(string $userId)
```

### 2. ✅ Contraintes sur les routes

**Avant** :
```php
Route::delete('sessions/{id}', [UserController::class, 'revokeSession']);
Route::post('users/{id}/restore', [UserController::class, 'restore']);
```

**Après** :
```php
Route::delete('sessions/{tokenId}', [UserController::class, 'revokeSession'])
    ->where('tokenId', '[0-9]+');
    
Route::post('users/{userId}/restore', [UserController::class, 'restore'])
    ->where('userId', '[0-9]+');
```

### 3. ✅ Nommage cohérent

Éviter les conflits avec le model binding de Laravel :
- ❌ `{id}` (trop générique)
- ✅ `{tokenId}`, `{userId}` (spécifique)

## Commandes à exécuter

```bash
# 1. Vider TOUS les caches
chmod +x clear-cache.sh
./clear-cache.sh

# OU manuellement
php artisan optimize:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 2. Redémarrer Valet
valet restart

# 3. Vérifier les routes
php artisan route:list --path=api

# 4. Tester l'accès
curl http://your-site.test/docs/api
```

## Vérification

### Routes qui causaient problème

Vérifiez que ces routes apparaissent correctement :

```bash
php artisan route:list | grep sessions
```

**Attendu** :
```
DELETE  api/user/sessions/{tokenId}  → UserController@revokeSession
```

```bash
php artisan route:list | grep restore
```

**Attendu** :
```
POST  api/admin/users/{userId}/restore  → UserController@restore
```

### Test direct

```bash
# Login
TOKEN=$(curl -s -X POST http://your-site.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@groupeka.com","password":"Admin@123!"}' \
  | jq -r '.data.token')

# Voir les sessions
curl -H "Authorization: Bearer $TOKEN" \
  http://your-site.test/api/user/sessions

# Supprimer une session (remplacez {tokenId} par un ID réel)
curl -X DELETE \
  -H "Authorization: Bearer $TOKEN" \
  http://your-site.test/api/user/sessions/1
```

## Autres causes possibles

### 1. Conflit de nommage avec Model Binding

Laravel fait du **route model binding** automatique. Si vous avez :
```php
Route::get('users/{user}', ...); // Injecte le model User
Route::delete('sessions/{id}', ...); // Cherche un model avec 'id'
```

**Solution** : Utilisez des noms explicites comme `{tokenId}`, `{userId}`.

### 2. Cache Valet

```bash
# Redémarrer complètement Valet
valet stop
valet start

# Ou
valet restart
```

### 3. Cache Opcache (si activé)

```bash
# Redémarrer PHP-FPM
sudo systemctl restart php8.2-fpm

# Ou vider opcache
php -r "opcache_reset();"
```

### 4. Problème de permissions

```bash
# Donner les bonnes permissions
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

## Checklist de débogage

- [ ] Vider tous les caches Laravel
- [ ] Redémarrer Valet
- [ ] Vérifier `php artisan route:list`
- [ ] Typer strictement les paramètres de méthodes
- [ ] Ajouter des contraintes `->where()` sur les routes
- [ ] Utiliser des noms de paramètres explicites
- [ ] Tester avec curl
- [ ] Vérifier les logs : `tail -f storage/logs/laravel.log`
- [ ] Accéder à Scramble : `http://your-site.test/docs/api`

## Si l'erreur persiste

### Activer le mode debug complet

Dans `.env` :
```env
APP_DEBUG=true
APP_ENV=local
LOG_LEVEL=debug
```

### Vérifier les logs détaillés

```bash
tail -f storage/logs/laravel.log
```

### Désactiver temporairement Scramble

Dans `config/scramble.php` :
```php
'enabled' => env('SCRAMBLE_ENABLED', false),
```

Puis dans `.env` :
```env
SCRAMBLE_ENABLED=false
```

Testez si vos routes fonctionnent sans Scramble :
```bash
curl http://your-site.test/api/user/profile
```

Si ça marche, le problème vient de Scramble. Sinon, c'est Laravel.

### Réinstaller les dépendances Scramble

```bash
composer remove dedoc/scramble
composer require dedoc/scramble --dev
php artisan vendor:publish --provider="Dedoc\Scramble\ScrambleServiceProvider"
```

## Résultat attendu

Après avoir appliqué les corrections :

✅ `http://your-site.test/docs/api` charge correctement  
✅ Toutes les routes s'affichent dans Scramble  
✅ Les endpoints protégés ont l'icône 🔒  
✅ Le bouton "Authorize" fonctionne  
✅ Aucune erreur dans les logs  

## Contact Support

Si le problème persiste malgré toutes ces étapes :

1. **Vérifier la version de Scramble** :
```bash
composer show dedoc/scramble
```

2. **Créer un issue GitHub** avec :
   - Version de Laravel
   - Version de Scramble
   - Logs complets
   - Routes problématiques
   - Stack trace complet