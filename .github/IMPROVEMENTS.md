# Mejoras Propuestas para Lara100 v1.1.0

## 🎯 Objetivos de v1.1.0

1. **Redondeo configurable** (crítico para finanzas internacionales)
2. **Soporte opcional para BCMath** (mayor precisión)
3. **Mantener simplicidad** (backward compatible)

---

## 1. Redondeo Configurable

### Implementación Propuesta

```php
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Base100 implements CastsAttributes
{
    public function __construct(
        protected int $roundingMode = PHP_ROUND_HALF_UP
    ) {}

    public function get(Model $model, string $key, mixed $value, array $attributes): float
    {
        if ($value === null) {
            return 0.0;
        }

        return round((float) $value / 100, 2, $this->roundingMode);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        if ($value === null) {
            return 0;
        }

        return (int) round((float) $value * 100, 0, $this->roundingMode);
    }
}
```

### Uso

```php
use AichaDigital\Lara100\Casts\Base100;

class Invoice extends Model
{
    protected function casts(): array
    {
        return [
            // Default (Round Half Up - España/CEE)
            'subtotal' => Base100::class,
            
            // Banker's Rounding (para contabilidad)
            'tax' => new Base100(PHP_ROUND_HALF_EVEN),
            
            // Custom rounding
            'discount' => new Base100(PHP_ROUND_HALF_DOWN),
        ];
    }
}
```

---

## 2. Soporte para BCMath (Opcional)

### ¿Por qué BCMath?

**`bcmath`** es una extensión PHP para aritmética de precisión arbitraria:
- ✅ Más preciso que `float` para valores muy grandes
- ✅ Evita overflow en operaciones
- ✅ Usado por brick/money y moneyphp/money

### Implementación

```php
class Base100 implements CastsAttributes
{
    public function __construct(
        protected int $roundingMode = PHP_ROUND_HALF_UP,
        protected bool $useBcmath = false
    ) {
        if ($this->useBcmath && !extension_loaded('bcmath')) {
            $this->useBcmath = false;
        }
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): float
    {
        if ($value === null) {
            return 0.0;
        }

        if ($this->useBcmath) {
            // BCMath divide con escala 2
            return (float) bcdiv((string) $value, '100', 2);
        }

        return round((float) $value / 100, 2, $this->roundingMode);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        if ($value === null) {
            return 0;
        }

        if ($this->useBcmath) {
            // BCMath multiply con escala 0
            return (int) bcmul((string) $value, '100', 0);
        }

        return (int) round((float) $value * 100, 0, $this->roundingMode);
    }
}
```

### Uso

```php
protected function casts(): array
{
    return [
        // Con BCMath para máxima precisión
        'balance' => new Base100(useBcmath: true),
        
        // BCMath + Banker's Rounding
        'revenue' => new Base100(
            roundingMode: PHP_ROUND_HALF_EVEN,
            useBcmath: true
        ),
    ];
}
```

---

## 3. Named Constructors (Alternative API)

Para mejor developer experience:

```php
class Base100 implements CastsAttributes
{
    // Named constructors
    public static function default(): self
    {
        return new self(PHP_ROUND_HALF_UP);
    }

    public static function bankers(): self
    {
        return new self(PHP_ROUND_HALF_EVEN);
    }

    public static function withBcmath(): self
    {
        return new self(useBcmath: true);
    }

    public static function custom(int $mode, bool $bcmath = false): self
    {
        return new self($mode, $bcmath);
    }
}
```

### Uso

```php
protected function casts(): array
{
    return [
        'price' => Base100::default(),
        'tax' => Base100::bankers(),
        'balance' => Base100::withBcmath(),
    ];
}
```

---

## 4. Trait Mejorado

```php
trait HasBase100
{
    protected int $base100RoundingMode = PHP_ROUND_HALF_UP;
    protected bool $base100UseBcmath = false;

    protected function initializeHasBase100(): void
    {
        foreach ($this->base100Attributes() as $attribute) {
            $this->casts[$attribute] = new Base100(
                $this->base100RoundingMode,
                $this->base100UseBcmath
            );
        }
    }

    abstract protected function base100Attributes(): array;
}
```

---

## 📊 Reglas de Redondeo por País/Contexto

### España y Unión Europea
- **Estándar:** Round Half Up (PHP_ROUND_HALF_UP)
- **Fiscal:** Generalmente Round Half Up
- **Tu uso actual:** ✅ Correcto

### Contextos Especiales

| Contexto | Modo | Razón |
|----------|------|-------|
| **Contabilidad general** | Banker's Rounding | Evita sesgo acumulativo |
| **IVA en España** | Round Half Up | Según normativa |
| **Bolsa de valores** | Round Half Even | Estándar financiero |
| **Cálculos fiscales** | Varía por país | Consultar normativa |

---

## 🔍 Verificar Redondeo Actual

Tu código actual usa:
```php
return (int) round((float) $value * 100);  // Sin especificar modo
```

Esto es **`PHP_ROUND_HALF_UP` por defecto**, que es:
- ✅ Correcto para España/CEE
- ✅ Correcto para la mayoría de casos
- ⚠️ No configurable por el usuario

---

## 🚀 Plan para v1.1.0

### Cambios Propuestos (MINOR - backward compatible)

1. ✅ Añadir constructor opcional con `$roundingMode`
2. ✅ Soporte opcional para BCMath
3. ✅ Named constructors para mejor DX
4. ✅ Documentar reglas de redondeo
5. ✅ Tests para cada modo de redondeo
6. ✅ Mantener comportamiento default (backward compatible)

### Tests Adicionales Necesarios

```php
it('supports different rounding modes', function () {
    $halfUp = new Base100(PHP_ROUND_HALF_UP);
    $halfEven = new Base100(PHP_ROUND_HALF_EVEN);
    
    // 0.555 con Half Up
    expect($halfUp->set($model, 'price', 0.555, []))->toBe(56);
    
    // 0.555 con Half Even (Banker's)
    expect($halfEven->set($model, 'price', 0.555, []))->toBe(56);
    
    // 0.545 con Half Up
    expect($halfUp->set($model, 'price', 0.545, []))->toBe(55);
    
    // 0.545 con Half Even (Banker's) 
    expect($halfEven->set($model, 'price', 0.545, []))->toBe(54);
});
```

---

## 📝 Recomendación

**Para v1.0.0 (ahora):**
- ✅ Dejar como está (simple, funciona)
- ✅ Documentar que usa Round Half Up

**Para v1.1.0 (próxima versión):**
- ✅ Añadir redondeo configurable
- ✅ Opcional BCMath support
- ✅ Documentar modos de redondeo

¿Te parece bien este plan?

