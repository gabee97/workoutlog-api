# Workout Log API

Uma API robusta para gerenciamento de logs de treino, construída com **Laravel 12**. Esta API permite que usuários gerenciem seus próprios exercícios, grupos musculares e rotinas de treino, além de interagir com um catálogo global de exercícios pré-definidos.

## 🚀 Funcionalidades

- **Autenticação Segura:** Utiliza Laravel Sanctum para autenticação via token.
- **Catálogo de Exercícios & Grupos Musculares:**
    - Catálogo Global: Exercícios e grupos musculares padrão disponíveis para todos.
    - Personalização: Usuários podem criar seus próprios exercícios e grupos musculares.
    - Visibilidade: Capacidade de ocultar itens globais da sua lista pessoal.
- **Gerenciamento de Treinos:**
    - Criação de rotinas de treino personalizadas.
    - Adição de exercícios específicos a cada treino.
    - Definição de séries (sets) com peso, repetições, tempo de descanso e RIR (Reps in Reserve).
    - Clonagem de treinos existentes para facilitar a criação de novas rotinas.
- **Respostas Padronizadas:** Todas as respostas da API seguem um formato consistente através do `ApiResponse`.

## 🛠️ Tecnologias

- **Framework:** Laravel 12
- **Autenticação:** Laravel Sanctum
- **Banco de Dados:** MySQL / PostgreSQL / SQLite
- **Linguagem:** PHP 8.2+

## ⚙️ Instalação e Configuração

### Pré-requisitos
- PHP 8.2 ou superior
- Composer
- Docker (opcional, via Laravel Sail)

### Passos para Instalação

1.  **Clone o repositório:**
    ```bash
    git clone https://github.com/seu-usuario/workoutlog-api.git
    cd workoutlog-api
    ```

2.  **Instale as dependências e configure o ambiente:**
    O projeto inclui um script de setup facilitado no `composer.json`:
    ```bash
    composer run setup
    ```
    *Este comando instalará dependências, criará o arquivo `.env`, gerará a chave da aplicação e executará as migrations.*

3.  **Inicie o servidor de desenvolvimento:**
    ```bash
    php artisan serve
    ```
    Ou use o Laravel Sail para rodar via Docker:
    ```bash
    ./vendor/bin/sail up
    ```

## 📖 Documentação da API (Endpoints Principais)

Todos os endpoints da API estão sob o prefixo `/api/v1`.

### Autenticação
- `POST /login`: Autenticação de usuário.
- `POST /register`: Cadastro de novo usuário.
- `GET /me`: Detalhes do usuário autenticado (requer token).
- `POST /logout`: Revogação do token de acesso.

### Grupos Musculares & Exercícios
- `GET /muscle-groups`: Lista grupos musculares visíveis.
- `POST /muscle-groups`: Cria um grupo muscular personalizado.
- `POST /muscle-groups/{id}/hide`: Oculta um grupo muscular global.
- `GET /exercises`: Lista exercícios disponíveis.
- `POST /exercises`: Cria um exercício personalizado.

### Treinos (Workouts)
- `GET /workouts`: Lista os treinos do usuário.
- `POST /workouts`: Cria um novo treino.
- `POST /workouts/{id}/clone`: Clona um treino existente.
- `POST /workouts/{id}/exercises`: Adiciona um exercício ao treino.
- `POST /workout-exercises/{id}/sets`: Adiciona uma série (set) a um exercício de um treino.

## 📄 Licença

Este projeto está sob a licença [MIT](LICENSE).
