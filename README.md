# 🌸 Essence — E-commerce de Perfumes & Cosméticos

Plataforma de e-commerce completa para venda de perfumes e cosméticos masculinos e femininos, inspirada na experiência do Boticário.

---

## 📋 Requisitos

- PHP >= 8.2
- Composer
- Node.js >= 18 + npm
- MySQL 8.0+ ou PostgreSQL 14+
- Redis (opcional, para cache/filas)

---

## 🚀 Instalação

### 1. Clonar e instalar dependências

```bash
git clone <repo-url> essence-shop
cd essence-shop

composer install
npm install
```

### 2. Configurar ambiente

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configurar banco de dados no .env

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=essence_shop
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

### 4. Rodar migrations e seeders

```bash
php artisan migrate --seed
```

### 5. Compilar assets e iniciar

```bash
npm run dev
php artisan serve
```

Acesse: **http://localhost:8000**

---

## 🗂️ Estrutura do Projeto

```
essence-shop/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/               # Login, registro, senha
│   │   │   ├── ProductController   # Catálogo de produtos
│   │   │   ├── CartController      # Carrinho de compras
│   │   │   ├── OrderController     # Pedidos
│   │   │   ├── CheckoutController  # Checkout/pagamento
│   │   │   └── Admin/              # Painel administrativo
│   │   └── Middleware/
│   │       └── AdminMiddleware.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Cart.php
│   │   └── Review.php
│   └── Services/
│       ├── CartService.php
│       ├── PaymentService.php
│       └── StockService.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── layouts/           # Templates base (app.blade.php, admin.blade.php)
│       ├── products/          # Listagem, detalhe, busca
│       ├── cart/              # Carrinho
│       ├── checkout/          # Etapas de checkout
│       ├── orders/            # Histórico de pedidos
│       ├── auth/              # Login e registro
│       └── admin/             # Painel administrativo
└── routes/
    ├── web.php
    └── api.php
```

---

## 🛍️ Funcionalidades

### Loja (Cliente)
- ✅ Catálogo com filtros (gênero, categoria, preço, marca)
- ✅ Busca de produtos
- ✅ Página de detalhe com galeria e avaliações
- ✅ Carrinho de compras (sessão + banco para logados)
- ✅ Lista de desejos (wishlist)
- ✅ Checkout em etapas (endereço → frete → pagamento)
- ✅ Histórico de pedidos
- ✅ Avaliações e notas de produtos
- ✅ Login com Google (OAuth)

### Painel Admin
- ✅ Dashboard com métricas
- ✅ CRUD de produtos com upload de imagens
- ✅ Gestão de categorias e marcas
- ✅ Gestão de pedidos e status
- ✅ Gestão de usuários
- ✅ Relatórios de vendas

---

## 💳 Pagamentos

Integração configurada para:
- **Mercado Pago** (cartão, PIX, boleto)
- **PagSeguro** (opcional)

Configure no `.env`:
```env
MERCADOPAGO_PUBLIC_KEY=sua_chave_publica
MERCADOPAGO_ACCESS_TOKEN=seu_token
```

---

## 📦 Principais Packages Laravel

| Package | Uso |
|---|---|
| `laravel/breeze` | Autenticação |
| `spatie/laravel-permission` | Permissões e roles |
| `intervention/image` | Processamento de imagens |
| `laravel/socialite` | Login social (Google) |
| `spatie/laravel-sluggable` | Slugs amigáveis |
| `livewire/livewire` | Componentes reativos (carrinho) |

---

## 🎨 Frontend

- **Blade Templates** com **Tailwind CSS**
- **Alpine.js** para interatividade leve
- **Livewire** para carrinho em tempo real
- Design responsivo (mobile-first)

---

## 🔧 Comandos Úteis

```bash
# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Rodar filas (emails, notificações)
php artisan queue:work

# Gerar storage link para imagens
php artisan storage:link

# Rodar testes
php artisan test
```

---

## 📄 Licença

MIT License
