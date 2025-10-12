# Workflow de Release con GitHub CLI (`gh`)

Esta guía documenta el proceso completo de release usando `gh` CLI para futuras versiones.

## 📋 Pre-requisitos

```bash
# Instalar GitHub CLI (si no está instalado)
brew install gh

# Autenticar (primera vez)
gh auth login
```

---

## 🚀 Workflow Completo para Nuevo Release

### 1️⃣ Crear Feature/Release Branch

```bash
# Desde main
git checkout main
git pull origin main

# Crear branch
git checkout -b release/v1.0.3

# O para features
git checkout -b feature/nueva-funcionalidad
```

---

### 2️⃣ Hacer Cambios y Validar Calidad

```bash
# Hacer cambios en el código
# ... editar archivos ...

# Validar calidad ANTES de commit
composer precommit

# Si precommit falla, arreglar y repetir
```

---

### 3️⃣ Commit con Conventional Commits

```bash
# Staging
git add .

# Commit siguiendo Conventional Commits
git commit -m "feat: nueva funcionalidad increíble"
# o
git commit -m "fix: corregir bug crítico"
# o
git commit -m "feat!: breaking change importante"
```

**Tipos de commits:**
- `feat:` → Minor version (1.0.2 → 1.1.0)
- `fix:` → Patch version (1.0.2 → 1.0.3)
- `feat!:` → Major version (1.0.2 → 2.0.0)
- `docs:` → No afecta versión
- `refactor:` → No afecta versión

---

### 4️⃣ Push de la Branch

```bash
git push -u origin release/v1.0.3
```

---

### 5️⃣ Crear Pull Request con `gh`

```bash
# Opción 1: PR interactivo (recomendado)
gh pr create

# Te preguntará:
# - Title: "Release v1.0.3 - Descripción corta"
# - Body: Descripción detallada o referencia a CHANGELOG

# Opción 2: PR con parámetros
gh pr create \
  --title "Release v1.0.3 - Nueva funcionalidad" \
  --body "Ver CHANGELOG.md para detalles completos" \
  --base main \
  --head release/v1.0.3

# Opción 3: PR con archivo de descripción
gh pr create \
  --title "Release v1.0.3" \
  --body-file .github/pr-template.md
```

---

### 6️⃣ Verificar Quality Gates en GitHub

```bash
# Ver estado del PR
gh pr status

# Ver checks del PR
gh pr checks

# Ver detalles de un check específico
gh run view
```

**Quality Gates que se ejecutan automáticamente:**
- ✅ Code Format (Pint)
- ✅ PHPStan MAX
- ✅ Tests
- ✅ Code Coverage 100%
- ✅ Mutation Score 70%+

---

### 7️⃣ Si Quality Gates Fallan ❌

```bash
# Ver logs del workflow
gh run view --log-failed

# Reproducir localmente
composer quality-full

# Arreglar el problema
# ... fix ...

# Validar localmente
composer precommit

# Commit y push del fix
git add .
git commit -m "fix: corregir quality gate"
git push

# GitHub Actions re-ejecuta automáticamente
```

---

### 8️⃣ Hacer Merge con `gh`

```bash
# Una vez que Quality Gates pasan ✅

# Opción 1: Squash merge (RECOMENDADO para releases)
gh pr merge --squash --delete-branch

# Opción 2: Regular merge
gh pr merge --merge --delete-branch

# Opción 3: Rebase merge
gh pr merge --rebase --delete-branch
```

**Nota:** `--delete-branch` elimina la rama automáticamente después del merge.

---

### 9️⃣ Actualizar CHANGELOG y Crear Tag

```bash
# Pull de main después del merge
git checkout main
git pull origin main

# Editar CHANGELOG.md
# ... agregar entrada para v1.0.3 ...

# Commit del changelog
git add CHANGELOG.md
git commit -m "docs: update CHANGELOG for v1.0.3"
git push origin main

# Crear tag anotado
git tag -a v1.0.3 -m "Release v1.0.3

- Nueva funcionalidad 1
- Nueva funcionalidad 2
- Fix bug X

Ver CHANGELOG.md para detalles completos."

# Push del tag
git push origin v1.0.3
```

---

### 🔟 Crear GitHub Release (Opcional)

```bash
# Opción 1: Release interactivo
gh release create v1.0.3

# Opción 2: Release automático desde CHANGELOG
gh release create v1.0.3 \
  --title "v1.0.3 - Título del Release" \
  --notes "Ver CHANGELOG.md para detalles completos" \
  --latest

# Opción 3: Release con notas auto-generadas
gh release create v1.0.3 --generate-notes
```

---

## 📋 Workflow Rápido (Todo en Uno)

Para releases simples, todo el proceso:

```bash
# 1. Feature branch
git checkout -b release/v1.0.3

# 2. Hacer cambios
# ...

# 3. Validar
composer precommit

# 4. Commit y push
git add .
git commit -m "feat: nueva funcionalidad"
git push -u origin release/v1.0.3

# 5. Crear PR
gh pr create --title "Release v1.0.3" --body "Ver CHANGELOG.md"

# 6. Esperar Quality Gates ✅

# 7. Merge
gh pr merge --squash --delete-branch

# 8. Tag y release
git checkout main
git pull
git tag -a v1.0.3 -m "Release v1.0.3"
git push origin v1.0.3
gh release create v1.0.3 --generate-notes
```

---

## 🎯 Aliases Útiles para `.zshrc` o `.bashrc`

```bash
# Agregar a tu shell config

# Quality check rápido
alias lara-quality="composer precommit"

# Release workflow completo
function lara-release() {
    VERSION=$1
    DESCRIPTION=$2
    
    if [ -z "$VERSION" ]; then
        echo "Usage: lara-release v1.0.3 'Descripción del release'"
        return 1
    fi
    
    # Crear branch
    git checkout -b release/$VERSION
    
    echo "✅ Branch creada. Ahora:"
    echo "1. Haz tus cambios"
    echo "2. Ejecuta: composer precommit"
    echo "3. Ejecuta: lara-pr '$DESCRIPTION'"
}

function lara-pr() {
    TITLE=$1
    
    # Validar calidad
    composer precommit || return 1
    
    # Commit y push
    git add .
    git commit -m "$TITLE" || return 1
    git push -u origin $(git branch --show-current)
    
    # Crear PR
    gh pr create --title "$TITLE" --body "Ver CHANGELOG.md para detalles"
    
    echo "✅ PR creado. Esperando Quality Gates..."
}

function lara-merge() {
    gh pr merge --squash --delete-branch
    git checkout main
    git pull origin main
    echo "✅ Merged. Ahora actualiza CHANGELOG y crea tag."
}
```

**Uso:**
```bash
lara-release v1.0.3 "Nueva funcionalidad"
# ... hacer cambios ...
composer precommit
lara-pr "feat: nueva funcionalidad increíble"
# ... esperar quality gates ...
lara-merge
```

---

## 🔍 Comandos Útiles de `gh`

### Ver PRs

```bash
# Listar PRs abiertos
gh pr list

# Ver detalles de un PR
gh pr view 2

# Ver status
gh pr status

# Ver checks
gh pr checks
```

### Ver Workflows/Actions

```bash
# Listar workflows
gh workflow list

# Ver runs recientes
gh run list

# Ver detalles de un run
gh run view <run-id>

# Ver logs
gh run view <run-id> --log

# Re-ejecutar workflow fallido
gh run rerun <run-id>
```

### Tags y Releases

```bash
# Listar releases
gh release list

# Ver detalles de un release
gh release view v1.0.2

# Descargar assets de un release
gh release download v1.0.2
```

---

## ✅ Checklist para Próximos Releases

- [ ] Crear branch desde main actualizado
- [ ] Hacer cambios necesarios
- [ ] Actualizar CHANGELOG.md con nueva versión
- [ ] Ejecutar `composer quality-full` localmente
- [ ] Commit con Conventional Commits
- [ ] Push de branch
- [ ] Crear PR con `gh pr create`
- [ ] Esperar Quality Gates ✅ en GitHub
- [ ] Mergear con `gh pr merge --squash --delete-branch`
- [ ] Pull de main
- [ ] Crear tag: `git tag -a v1.0.3 -m "..."`
- [ ] Push tag: `git push origin v1.0.3`
- [ ] (Opcional) Crear release: `gh release create v1.0.3`

---

## 🧹 Gestión de Ramas en Packagist

### Problema
Por defecto, **todas las ramas remotas** aparecen en Packagist como versiones instalables con prefijo `dev-` (ej: `dev-refactor/algo`, `dev-feature/nueva`), lo cual contamina las versiones disponibles.

### Solución Implementada
El `composer.json` está configurado con:

```json
{
    "non-feature-branches": ["main", "develop"],
    "extra": {
        "branch-alias": {
            "dev-main": "1.x-dev"
        }
    }
}
```

**Esto significa:**
- ✅ Solo `main` y `develop` aparecen como versiones dev en Packagist
- ❌ Todas las demás ramas (`feature/*`, `release/*`, `refactor/*`) son ignoradas
- 🎯 `dev-main` se mapea a `1.x-dev` para mejor versionado

### Buenas Prácticas

1. **Siempre eliminar la rama remota después del merge:**
   ```bash
   gh pr merge --squash --delete-branch
   ```
   El flag `--delete-branch` elimina automáticamente la rama del remoto.

2. **Si olvidaste eliminar una rama, hazlo manualmente:**
   ```bash
   # Ver ramas remotas
   git branch -r
   
   # Eliminar rama remota
   git push origin --delete feature/nombre-rama
   ```

3. **Limpiar ramas locales huérfanas:**
   ```bash
   # Actualizar referencias remotas
   git fetch --prune
   
   # Ver ramas locales sin remoto
   git branch -vv | grep 'gone]'
   
   # Eliminar ramas locales huérfanas
   git branch -D nombre-rama
   ```

### Packagist y Sincronización

- Packagist se sincroniza automáticamente vía webhook
- Los cambios en `composer.json` pueden tardar unos minutos en reflejarse
- Puedes forzar actualización: [Packagist Dashboard](https://packagist.org/packages/aichadigital/lara100) → "Update"
- Las ramas eliminadas del remoto desaparecerán de Packagist en la próxima sincronización

---

## 📚 Recursos

- [GitHub CLI Manual](https://cli.github.com/manual/)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [Semantic Versioning](https://semver.org/)
- [Quality System Guide](../QUALITY.md)

---

**Última actualización:** Después de v1.0.2 (Primera vez usando `gh` workflow)

