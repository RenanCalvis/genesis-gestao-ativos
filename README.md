# Genesis — Sistema de Gestão de Ativos em Saúde

Este projeto foi desenvolvido como uma solução full-stack robusta para o gerenciamento de patrimônios e controle de empréstimos entre estabelecimentos.

---

## 🧠 Decisões Arquiteturais e de Engenharia

Como o descritivo original do teste não especificava a natureza orgânica dos "estabelecimentos" e "patrimônios", optei por contextualizar o sistema como uma **Rede de Saúde**. Por isso, tipifiquei os estabelecimentos rigidamente por Enums (`HOSPITAL`, `CLINICA`, `LABORATORIO` e `AMBULATORIO`), trazendo extrema fidelidade e realismo ao bloqueio de que "empréstimos de equipamentos só podem ocorrer entre unidades da mesma natureza".

Para sustentar essa modelagem com performance e segurança de nível de produção, implementei quatro grandes pilares:

1. **Refatoração da Infraestrutura (Docker):** Alterei a estrutura original de containers para isolar completamente a aplicação (`app`) do banco de dados (`db`). Isso permite que o projeto seja rodado e avaliado sem a necessidade de instalar o PHP localmente. Além disso, injetei a configuração de fuso horário (`TZ: America/Campo_Grande`) direto no container do **PostgreSQL 14**, garantindo que as travas de SLA (horas de retirada e previsão de devolução) fiquem perfeitamente sincronizadas com o backend.
2. **Segurança com UUID v4:** Adotei identificadores universais gerados nativamente pelo banco (`gen_random_uuid()`). Isso impede que usuários mal-intencionados adivinhem IDs sequenciais na URL (prevenindo falhas de IDOR) e prepara a base para cenários de replicação e microsserviços.
3. **Performance e Paginação Blindada:** Para garantir que o sistema responda de forma instantânea mesmo com o crescimento da base, criei índices físicos (`addKey`) nas migrações. Indexei as chaves estrangeiras (acelerando o cruzamento de dados nos `JOINs`), as datas de empréstimo e a coluna de nome, que utiliza o recurso nativo do framework para buscas ignorando maiúsculas e minúsculas (_case-insensitive_), melhorando a experiência do usuário.
4. **Proteção contra Soft Deletes:** Como o ORM injeta silenciosamente a condição `WHERE deleted_at IS NULL` em todas as consultas de unidades, indexei essa coluna na própria migração. Isso evita que o banco seja forçado a ler a tabela inteira do zero a cada requisição.

---

## 🚀 Como Executar o Projeto

Você tem duas abordagens para rodar as migrações, os _seeders_ (que popularão o banco com dezenas de registros variados para validação visual da UI e paginação) e a suíte de Testes Unitários.

### ⚙️ Configuração Inicial Obrigatória

Antes de subir os serviços, crie o arquivo de variáveis de ambiente da aplicação copiando o modelo pré-configurado:

**No Mac ou Linux:**

```bash
cp app/.env.example app/.env
```

**No Windows (PowerShell / CMD):**

```bash
copy app\.env.example app\.env
```

**Opção 1: Usando apenas o Docker (Recomendado)**
```bash

# 1. Suba os containers em background (Banco de Dados e Aplicação)

docker-compose up -d

# 2. Recrie as tabelas com índices e popule com a massa de dados automatizada
docker-compose exec app php spark migrate:refresh && docker-compose exec app php spark db:seed DatabaseSeeder

# 3. Rode a suíte de testes unitários da camada de Serviços
docker-compose exec app php vendor/bin/phpunit tests

````

**No Windows (PowerShell / CMD):**
No Windows com Docker Desktop atualizado, geralmente não utilizamos o hífen entre as palavras:
```cmd
docker compose up -d
docker compose exec app php spark migrate:refresh
docker compose exec app php spark db:seed DatabaseSeeder
docker compose exec app php vendor/bin/phpunit tests
```

### Opção 2: Usando PHP Local

Se você já possui o **PHP 8.2+** configurado globalmente no terminal da sua máquina, , basta subir o banco via Docker e rodar o Spark nativamente dentro do diretório da aplicação:

```bash
# 1. Suba apenas a infraestrutura do banco
docker-compose up -d db

# 2. Entre na pasta da aplicação
cd app

# 3. Recrie a estrutura e alimente com dados de teste
php spark migrate:refresh && php spark db:seed DatabaseSeeder

# 4. Teste as regras de negócio isoladamente
vendor/bin/phpunit tests
```

## 🛡️ O Que Foi Entregue?

- **Camada de Serviço (Service Pattern)**: Cumprindo o requisito arquitetural, todo o coração do negócio foi isolado em Services (`AssetService`, `EstablishmentService`, `LoanService`), blindando os Controllers e tornando-os apenas roteadores de tráfego HTTP.
- **Cobertura de Testes na Regra de Negócio**: Validação rigorosa dos Guard Clauses (bloqueio de baixa dupla, expiração de `max_loan_days`, incompatibilidade de rede e proteção contra empréstimo de patrimônios inativos).
- **Performance de Banco**: Paginação 100% `server-side` sem vazamento de memória e injeção inteligente de Índices SQL (`addKey`) nas migrations.
- **Design Moderno (UI/UX)**: Interface completamente desenhada utilizando CSS Tokens e layout _clean_ minimalista, fugindo do aspecto genérico do Bootstrap puro.
