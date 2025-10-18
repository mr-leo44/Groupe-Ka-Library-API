# 🚀 Testing with Valet Linux

## Configuration Valet Linux

### 1. Vérifier que Valet est bien configuré

```bash
# Vérifier le statut
valet status

# Lister les sites
valet links

# Voir l'URL de votre projet
valet link groupe-ka-library
# ✅ Site [groupe-ka-library] linked to [http://groupe-ka-library.test]
```

### 2. Accéder à Scramble

Votre documentation API sera disponible à :

```
http://groupe-ka-library.test/docs/api
```

Ou selon votre configuration :
```
http://votre-nom-projet.test/docs/api
```

---

## 📋 Test complet du Logout

### Scénario 1 : Logout simple (device actuel)

#### Étape 1 : Login
```bash
# Via Scramble UI
POST http://groupe-ka-library.test/api/auth/login

Body:
{
  "email": "admin@groupeka.com",
  "password": "Admin@123!"
}

# Réponse
{
  "success": true,
  "data": {
    "user": {...},
    "token": "1|AbCdEf..."  # ⬅️ Copiez ce token
  }
}
```

#### Étape 2 : Authorize dans Scramble
1. Cliquez sur **"Authorize" 🔓** en haut à droite
2. Collez votre token (Scramble ajoute "Bearer " automatiquement)
3. Cliquez **"Authorize"**
4. Le cadenas devient **fermé 🔒**

#### Étape 3 : Tester Logout
```bash
# Dans Scramble UI
POST http://groupe-ka-library.test/api/auth/logout

# Devrait afficher 🔒 (authentification requise)

# Cliquez "Try it out" puis "Execute"

# Réponse attendue
{
  "success": true,
  "message": "Logged out successfully",
  "data": null
}
```

#### Étape 4 : Vérifier que le token est révoqué
```bash
# Essayez d'accéder à votre profil
GET http://groupe-ka-library.test/api/user/profile

# Réponse attendue (401)
{
  "message": "Unauthenticated"
}
```

✅ **Le logout fonctionne !** Le token est bien révoqué.

---

### Scénario 2 : Logout All Devices

#### Étape 1 : Créer plusieurs sessions

**Session 1 - Mobile**
```bash
POST http://groupe-ka-library.test/api/auth/login
{
  "email": "admin@groupeka.com",
  "password": "Admin@123!",
  "device_name": "mobile-app"
}
# Token1: 1|MobileToken...
```

**Session 2 - Tablet**
```bash
POST http://groupe-ka-library.test/api/auth/login
{
  "email": "admin@groupeka.com",
  "password": "Admin@123!",
  "device_name": "tablet"
}
# Token2: 2|TabletToken...
```

**Session 3 - Desktop** (utilisez celle-ci dans Scramble)
```bash
POST http://groupe-ka-library.test/api/auth/login
{
  "email": "admin@groupeka.com",
  "password": "Admin@123!",
  "device_name": "desktop"
}
# Token3: 3|DesktopToken...
```

#### Étape 2 : Vérifier les sessions actives
```bash
# Avec le Token3 (desktop)
GET http://groupe-ka-library.test/api/user/sessions

# Réponse (devrait montrer 3 sessions)
{
  "success": true,
  "data": [
    {"id": 1, "name": "mobile-app", "is_current": false},
    {"id": 2, "name": "tablet", "is_current": false},
    {"id": 3, "name": "desktop", "is_current": true}  # ⬅️ Session actuelle
  ]
}
```

#### Étape 3 : Logout All Devices
```bash
POST http://groupe-ka-library.test/api/auth/logout-all

# Réponse
{
  "success": true,
  "message": "Logged out from all devices"
}
```

#### Étape 4 : Vérifier que TOUS les tokens sont révoqués
```bash
# Essayer avec Token1 (mobile)
curl -H "Authorization: Bearer $TOKEN1" \
  http://groupe-ka-library.test/api/user/profile
# ❌ 401 Unauthenticated

# Essayer avec Token2 (tablet)
curl -H "Authorization: Bearer $TOKEN2" \
  http://groupe-ka-library.test/api/user/profile
# ❌ 401 Unauthenticated

# Essayer avec Token3 (desktop)
curl -H "Authorization: Bearer $TOKEN3" \
  http://groupe-ka-library.test/api/user/profile
# ❌ 401 Unauthenticated
```

✅ **Logout all fonctionne !** Tous les tokens sont révoqués.

---

## 🔍 Vérification dans Scramble

### Checklist des endpoints Logout

Ouvrez `http://groupe-ka-library.test/docs/api` et vérifiez :

#### ✅ POST /api/auth/logout
- [ ] Icône 🔒 visible
- [ ] Tag "Authentication"
- [ ] Description : "Logout current device"
- [ ] Bouton "Authorize" requis pour tester
- [ ] Réponse 200 : `{"success": true, "message": "Logged out successfully"}`
- [ ] Réponse 401 : `{"message": "Unauthenticated"}`

#### ✅ POST /api/auth/logout-all
- [ ] Icône 🔒 visible
- [ ] Tag "Authentication"
- [ ] Description : "Logout all devices"
- [ ] Bouton "Authorize" requis pour tester
- [ ] Réponse 200 : `{"success": true, "message": "Logged out from all devices"}`

---

## 🧪 Tests avec curl (alternative à Scramble)

### Test Logout Simple

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://groupe-ka-library.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@groupeka.com","password":"Admin@123!"}' \
  | jq -r '.data.token')

echo "Token: $TOKEN"

# 2. Vérifier que ça marche
curl -H "Authorization: Bearer $TOKEN" \
  http://groupe-ka-library.test/api/user/profile
# ✅ Devrait afficher votre profil

# 3. Logout
curl -X POST \
  -H "Authorization: Bearer $TOKEN" \
  http://groupe-ka-library.test/api/auth/logout
# ✅ {"success":true,"message":"Logged out successfully"}

# 4. Vérifier que le token est révoqué
curl -H "Authorization: Bearer $TOKEN" \
  http://groupe-ka-library.test/api/user/profile
# ❌ {"message":"Unauthenticated"}
```

### Test Logout All

```bash
# 1. Login 3 fois
TOKEN1=$(curl -s -X POST http://groupe-ka-library.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@groupeka.com","password":"Admin@123!","device_name":"mobile"}' \
  | jq -r '.data.token')

TOKEN2=$(curl -s -X POST http://groupe-ka-library.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@groupeka.com","password":"Admin@123!","device_name":"tablet"}' \
  | jq -r '.data.token')

TOKEN3=$(curl -s -X POST http://groupe-ka-library.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@groupeka.com","password":"Admin@123!","device_name":"desktop"}' \
  | jq -r '.data.token')

# 2. Voir les sessions
curl -H "Authorization: Bearer $TOKEN3" \
  http://groupe-ka-library.test/api/user/sessions
# ✅ Devrait montrer 3 sessions

# 3. Logout all
curl -X POST \
  -H "Authorization: Bearer $TOKEN3" \
  http://groupe-ka-library.test/api/auth/logout-all
# ✅ {"success":true,"message":"Logged out from all devices"}

# 4. Vérifier que tous sont révoqués
curl -H "Authorization: Bearer $TOKEN1" http://groupe-ka-library.test/api/user/profile
# ❌ Unauthenticated
curl -H "Authorization: Bearer $TOKEN2" http://groupe-ka-library.test/api/user/profile
# ❌ Unauthenticated
curl -H "Authorization: Bearer $TOKEN3" http://groupe-ka-library.test/api/user/profile
# ❌ Unauthenticated
```

---

## 🐛 Troubleshooting Valet

### Site non accessible

```bash
# Relancer Valet
valet restart

# Vérifier les services
valet status

# Relink le site
cd /chemin/vers/votre/projet
valet link groupe-ka-library
```

### Erreur 502 Bad Gateway

```bash
# Redémarrer PHP-FPM
sudo systemctl restart php8.2-fpm

# Ou
valet restart
```

### HTTPS non activé

```bash
# Sécuriser le site
valet secure groupe-ka-library

# Accès via
https://groupe-ka-library.test/docs/api
```

### Vider le cache

```bash
# Cache Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Cache navigateur
# Ctrl + Shift + R dans le navigateur
```

---

## 📊 Résumé des URLs Valet

| Service | URL |
|---------|-----|
| **Documentation API** | http://groupe-ka-library.test/docs/api |
| **Login** | POST http://groupe-ka-library.test/api/auth/login |
| **Logout** | POST http://groupe-ka-library.test/api/auth/logout |
| **Logout All** | POST http://groupe-ka-library.test/api/auth/logout-all |
| **Profile** | GET http://groupe-ka-library.test/api/user/profile |
| **Sessions** | GET http://groupe-ka-library.test/api/user/sessions |

---

## ✅ Conclusion

**OUI, le logout EST bien pris en compte dans Scramble !** 🎉

Les deux endpoints sont correctement documentés avec :
- ✅ Annotation `@authenticated`
- ✅ Icône de cadenas 🔒
- ✅ Exemples de réponses
- ✅ Documentation des erreurs
- ✅ Descriptions claires

Pour tester :
1. Ouvrez `http://votre-site.test/docs/api`
2. Cherchez "Authentication" dans la sidebar
3. Vous devriez voir :
   - 🔒 POST /api/auth/logout
   - 🔒 POST /api/auth/logout-all