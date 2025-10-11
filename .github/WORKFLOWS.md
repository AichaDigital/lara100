# GitHub Actions Workflows - Lara100

## 🎯 Filosofía

Los workflows están diseñados para **validar calidad automáticamente** antes de permitir merges o releases.

## 📋 Workflows Disponibles

### 1. **quality-gates.yml** - Control de Calidad (NUEVO)

**Cuándo se ejecuta:**
- ✅ En cada Pull Request a `main`
- ✅ En cada push a `main`

**Qué valida:**
1. 🎨 **Code Format** - Laravel Pint (debe pasar sin cambios)
2. 🔍 **PHPStan MAX** - Análisis estático nivel 9 + Bleeding Edge
3. 🧪 **Tests** - Todos los tests deben pasar
4. 📊 **Code Coverage** - Debe ser exactamente 100%
5. 🧬 **Mutation Testing** - Debe ser >= 70%

**Resultado:**
- ✅ Si todo pasa: PR puede ser merged
- ❌ Si algo falla: PR bloqueado hasta corregir

---

### 2. **tests.yml** - Tests Básicos (EXISTENTE)

Workflow simple que ejecuta tests en diferentes versiones de PHP y Laravel.

---

### 3. **phpstan.yml** - Análisis Estático (EXISTENTE)

Ejecuta PHPStan para análisis estático.

---

## 🚀 Workflow Recomendado para Developers

### Desarrollo Local

```bash
# 1. Crear feature branch
git checkout -b feature/mi-feature

# 2. Hacer cambios
# ... código ...

# 3. Antes de commit
composer precommit

# 4. Commit
git add .
git commit -m "feat: mi feature"

# 5. Push
git push origin feature/mi-feature
```

### Pull Request

```bash
# 6. Crear PR en GitHub
# → GitHub Actions ejecuta quality-gates.yml automáticamente

# 7a. Si PASA ✅
# → Reviewer aprueba
# → Merge a main
# → (Opcional) Auto-tag con semantic versioning

# 7b. Si FALLA ❌
# → Ver logs de GitHub Actions
# → Corregir localmente con:
#    - composer format (si falla Pint)
#    - composer phpstan (si falla análisis)
#    - composer test (si fallan tests)
#    - composer test-coverage (si falla coverage)
#    - composer test-mutate (si falla mutation)
# → Push de correcciones
# → GitHub Actions re-ejecuta automáticamente
```

---

## 🔒 Quality Gates - Prevención de Problemas

### ¿Qué previene?

❌ **SIN Quality Gates:**
- Código sin formatear entra a main
- Bugs detectables por PHPStan llegan a producción
- Coverage baja sin que nadie se dé cuenta
- Mutation score cae silenciosamente

✅ **CON Quality Gates:**
- Imposible mergear código mal formateado
- PHPStan MAX debe pasar siempre
- 100% coverage es obligatorio
- Mutation score >= 70% garantizado

---

## 📊 Métricas Requeridas para Merge

```yaml
✅ Pint Format:       0 errores de formato
✅ PHPStan:           Nivel MAX, 0 errores
✅ Tests:             Todos pasando
✅ Code Coverage:     Exactamente 100.0%
✅ Mutation Score:    Mínimo 70%
```

---

## 🎯 Auto-Tagging (Futuro - Opcional)

Se puede agregar un workflow que auto-tagee releases cuando:

1. PR se fusiona a `main`
2. Commit message sigue Conventional Commits
3. Todos los quality gates pasan

Ejemplo:
```
feat: nueva funcionalidad → v1.1.0 (minor)
fix: corrección de bug  → v1.0.3 (patch)
feat!: breaking change → v2.0.0 (major)
```

---

## 🚨 Troubleshooting

### "Quality Gates Failed" en GitHub Actions

**Pasos:**

1. **Ver el log específico** en GitHub Actions
2. **Reproducir localmente:**
   ```bash
   composer quality-full
   ```
3. **Corregir el problema**
4. **Push de fix** - Actions re-ejecuta automáticamente

### "Mutation score below 70%"

Si el mutation score cae:

1. Ejecutar localmente:
   ```bash
   composer test-mutate
   ```
2. Ver qué mutaciones nuevas aparecieron
3. Agregar tests específicos o usar `@pest-mutate-ignore` si son equivalentes
4. Push

---

## 💡 Recomendaciones

### Para el Equipo

1. **Ejecutar siempre `composer precommit`** antes de push
2. **No hacer commits directos a main** - usar PRs
3. **Revisar logs de Actions** cuando fallen
4. **Mantener quality-full pasando** localmente antes de PR

### Para Maintainers

1. **Aprobar PRs solo si Actions pasan** ✅
2. **Revisar coverage diffs** en PRs grandes
3. **Verificar mutation score** no baje
4. **Mantener documentación actualizada**

---

## 📚 Recursos

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Pest Mutation Testing](https://pestphp.com/docs/mutation-testing)
- [Quality System Guide](../QUALITY.md)
- [Mutation Analysis](../MUTATIONS.md)

---

**Resumen:** Los workflows garantizan que **solo código de calidad enterprise** llegue a main. 🛡️

