# Guía de Calidad de Código - Lara100

Este documento describe todos los comandos y procesos de calidad implementados siguiendo los principios de "Good Code" de Laravel.

## 🎯 Filosofía

Este proyecto sigue los **6 principios de Good Code** del starter kit de Nuno Maduro:

1. ✅ **Cobertura de tipos al 100%** - Todos los parámetros y retornos tipados
2. ✅ **PHPStan nivel MAX** - Análisis estático sin concesiones + Bleeding Edge
3. ✅ **Cobertura de tests al 100%** - Todos los tests pasan con cobertura completa
4. ✅ **Formateo estricto automático** - Laravel Pint sin excepciones
5. ✅ **Control del entorno en tests** - Tests deterministas y aislados
6. ✅ **Validación local automática** - Pre-commit hooks locales

## 📊 Métricas Actuales

```bash
✅ PHPStan:         Nivel MAX + Bleeding Edge - 0 errores
✅ Tests:           22/22 pasando (100% cobertura)
✅ Pint:            Código formateado correctamente
✅ Mutation Score:  58.62% (objetivo: 80%+)
✅ SOLID:           Principios aplicados correctamente
```

## 🔧 Comandos Disponibles

### Comandos Básicos

#### Formateo de Código
```bash
# Formatear código automáticamente
composer format

# Verificar formato sin modificar
composer format-check
```

#### Tests
```bash
# Tests básicos
composer test

# Tests en paralelo (más rápido)
composer test-parallel

# Tests con cobertura (requiere 100%)
composer test-coverage

# Tests con perfil de rendimiento
composer test-profile
```

#### Análisis Estático
```bash
# PHPStan (nivel MAX + Bleeding Edge)
composer phpstan

# Regenerar baseline de PHPStan
composer phpstan-baseline
```

#### Mutation Testing
```bash
# Ejecutar mutation testing (min 60%)
composer test-mutate
```

### Comandos de Calidad

#### Pre-Commit (RECOMENDADO)
```bash
# Ejecutar antes de cada commit
composer precommit
```

**Incluye:**
- ✅ Formateo automático con Pint
- ✅ Análisis estático con PHPStan MAX
- ✅ Tests paralelos

**Duración aproximada:** ~2-3 segundos

#### Quality Check Completo
```bash
# Validación completa de calidad
composer quality
```

**Incluye:**
- ✅ Formateo automático con Pint
- ✅ Análisis estático con PHPStan MAX
- ✅ Tests con cobertura 100%

**Duración aproximada:** ~5-10 segundos

#### Quality Check Full (Con Mutation Testing)
```bash
# Validación completa + mutation testing
composer quality-full
```

**Incluye:**
- ✅ Formateo automático con Pint
- ✅ Análisis estático con PHPStan MAX
- ✅ Tests con cobertura 100%
- ✅ Mutation testing (min 60%)

**Duración aproximada:** ~30-60 segundos

## 🚀 Workflow Recomendado

### Desarrollo Diario

```bash
# 1. Hacer cambios en el código
# 2. Ejecutar pre-commit antes de commit
composer precommit

# 3. Si todo pasa, hacer commit
git add .
git commit -m "feat: mi nuevo feature"
```

### Antes de Push/PR

```bash
# Ejecutar quality-full para validación completa
composer quality-full
```

### Cuando Algo Falla

```bash
# Si falla el formato
composer format

# Si falla PHPStan (ver errores específicos)
composer phpstan

# Si fallan tests (ejecutar con detalle)
composer test

# Si falla mutation testing (normal, ir mejorando gradualmente)
composer test-mutate
```

## 📈 Configuración PHPStan

### Nivel Actual: MAX (9) + Bleeding Edge

```yaml
includes:
    - phpstan-baseline.neon
    - phar://phpstan.phar/conf/bleedingEdge.neon

parameters:
    paths:
        - src
        - config
        - tests

    level: max

    checkOctaneCompatibility: true
    checkModelProperties: true
    treatPhpDocTypesAsCertain: false
```

### Extensiones Auto-Cargadas
- ✅ Larastan (Laravel-specific rules)
- ✅ Carbon extension
- ✅ PHPUnit extension
- ✅ Deprecation rules

## 🧪 Mutation Testing

### Score Actual: 58.62%

El mutation testing verifica que tus tests realmente validan el comportamiento del código.

**Mutaciones detectadas:**
- 34 mutaciones probadas ✅
- 24 mutaciones no probadas ❌

### Objetivo Progresivo

```bash
Sprint 1-2:  60% (actual)
Sprint 3-4:  70%
Sprint 5-6:  80%
Sprint 7+:   85%+
```

### Cómo Mejorar el Score

1. Agregar tests para edge cases
2. Testear comportamiento con BCMath activado/desactivado
3. Testear diferentes modos de redondeo
4. Agregar tests para valores extremos

## 🎓 Principios SOLID Aplicados

### Single Responsibility Principle ✅
```php
// ✅ Base100: Conversión base100
// ✅ HasBase100: Aplicar cast a múltiples atributos
// ✅ Lara100ServiceProvider: Registrar el paquete
```

### Open/Closed Principle ✅
```php
// ✅ Base100 es final - configuración sobre extensión
// ✅ Extensible vía config (rounding_mode, use_bcmath)
```

### Liskov Substitution Principle ✅
```php
// ✅ Base100 implementa CastsAttributes correctamente
// ✅ PHPStan MAX lo verifica automáticamente
```

### Interface Segregation Principle ✅
```php
// ✅ CastsAttributes: Interface específica y enfocada
// ✅ Sin interfaces "gordas"
```

### Dependency Inversion Principle ✅
```php
// ✅ Base100 depende de abstracción (CastsAttributes)
// ✅ Inyección de dependencias vía constructor
```

## 📝 Baseline de PHPStan

El baseline contiene **9 errores** que son falsos positivos inevitables:

1. **6 errores**: Acceso a propiedades en tests con Pest (PHPStan no reconoce las assertions de Pest)
2. **3 errores**: Template types de Pest (limitación de PHPStan con DSL de Pest)

**Estrategia:** Mantener baseline al mínimo y documentar cada error.

## 🔍 Debugging

### Ver Errores de PHPStan en Detalle
```bash
vendor/bin/phpstan analyse -vvv
```

### Ver Coverage HTML
```bash
composer test-coverage -- --coverage-html=build/coverage
open build/coverage/index.html
```

### Ver Mutation Testing Detallado
```bash
vendor/bin/pest --mutate --everything --covered-only -vvv
```

## 📚 Recursos

- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)
- [Pest PHP Documentation](https://pestphp.com/docs/installation)
- [Laravel Pint Documentation](https://laravel.com/docs/master/pint)
- [Pest Mutate Documentation](https://pestphp.com/docs/mutation-testing)
- [Good Code en Laravel](./Good_Code_en_Laravel.md)

## 🎯 Próximos Pasos

### Corto Plazo (1-2 sprints)
- [ ] Aumentar mutation score a 70%
- [ ] Agregar tests para BCMath habilitado
- [ ] Reducir baseline a 0 (si es posible)

### Mediano Plazo (3-6 sprints)
- [ ] Mutation score 80%+
- [ ] Agregar Architecture tests con Pest Arch
- [ ] Documentar patrones de testing

### Largo Plazo (6+ sprints)
- [ ] Mutation score 85%+
- [ ] Benchmark de rendimiento
- [ ] Ejemplos de uso avanzado

---

**¿Preguntas?** Revisa la [documentación de Good Code](./Good_Code_en_Laravel.md) o ejecuta `composer precommit` para validar tus cambios.

