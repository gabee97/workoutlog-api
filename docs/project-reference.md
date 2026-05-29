# WorkoutLog API - Referencia do Projeto

Este documento descreve o estado atual da API Laravel para controle de treinos, com foco em preparar o consumo por um front-end separado via JSON.

## Resumo Executivo

A API ja tem uma base clara para cadastro de usuarios, catalogo de grupos musculares/exercicios, modelos de treino, dias de treino, exercicios do dia e series planejadas. A autenticacao usa Laravel Sanctum com bearer token e as rotas estao versionadas em `/api/v1`.

Ainda nao recomendo iniciar o front-end como se a API estivesse estavel. Da para iniciar prototipacao de telas e contratos preliminares, mas existem ajustes obrigatorios antes de integrar seriamente:

- Rotas `show` de `muscle-groups` e `exercises` existem, mas os metodos nao existem nos controllers.
- `MuscleGroup` e `Exercise` nao incluem `owner_user_id` em `$fillable`, entao criacoes feitas por usuario tendem a cair como globais (`owner_user_id = 0`).
- A policy generica `OwnedCatalogPolicy` existe, mas nao esta registrada para `MuscleGroup` e `Exercise`; autorizacoes desses recursos podem falhar.
- O padrao de erro nao esta consistente: varios endpoints usam `$request->validate()` sem capturar `ValidationException`, retornando o formato padrao do Laravel em vez de `ApiResponse`.
- Existem migrations e seeders de estruturas antigas/novas parcialmente desalinhadas, especialmente `WorkoutSeeder` usando `workout_id` em `workout_exercises`, enquanto o modelo atual usa `workout_day_id`.
- Existem tabelas para registros de treino (`workout_logs`, `workout_log_exercises`) e programas do usuario (`user_workout_programs`), mas nao existem models, controllers ou rotas para esses fluxos.

## Stack e Arquitetura

- Framework: Laravel 12.
- PHP: `^8.2`.
- Autenticacao: Laravel Sanctum.
- Padrao de rotas: API versionada em `routes/api.php`, prefixo `/api/v1`.
- Padrao de resposta pretendido: `App\Support\ApiResponse`.
- Persistencia: Eloquent Models + migrations em `database/migrations`.
- Testes: PHPUnit/Laravel test runner, com exemplos e cobertura inicial para series de exercicio.

Estrutura principal:

- `app/Http/Controllers/Api/V1`: controllers REST da API.
- `app/Models`: models Eloquent.
- `app/Policies`: autorizacao por ownership.
- `app/Support/ApiResponse.php`: wrapper de resposta JSON.
- `routes/api.php`: definicao dos endpoints.
- `database/migrations`: schema do banco.
- `database/seeders`: dados iniciais de catalogo/treino.
- `tests/Feature`: testes de comportamento HTTP.

## Modulos Principais

### Autenticacao

Arquivos:

- Controller: `AuthController`
- Model: `User`
- Middleware: `auth:sanctum`
- Tabela: `users`, `personal_access_tokens`

Fluxo:

1. Usuario registra ou faz login.
2. API retorna `token` e `token_type = Bearer`.
3. Front-end envia `Authorization: Bearer <token>` nas rotas protegidas.
4. `GET /api/v1/me` retorna o usuario autenticado.
5. `POST /api/v1/logout` remove o token atual.

### Catalogo

Recursos:

- `MuscleGroup`: grupos musculares.
- `Exercise`: exercicios.
- Tabelas auxiliares de ocultacao por usuario:
  - `user_hidden_muscle_groups`
  - `user_hidden_exercises`

Conceito atual:

- `owner_user_id = 0`: item global/padrao.
- `owner_user_id = user.id`: item criado por usuario.
- `visibleTo($userId)`: retorna globais + itens do usuario.
- `hide/unhide`: oculta ou reexibe itens globais para um usuario.

Ponto critico: `owner_user_id` nao esta em `$fillable` de `MuscleGroup` e `Exercise`, apesar dos controllers tentarem atribuir esse campo.

### Treinos

Recursos:

- `Workout`: treino/modelo.
- `WorkoutDay`: dia dentro do treino.
- `WorkoutExercise`: exercicio configurado dentro de um dia.
- `WorkoutExerciseSet`: serie planejada de um exercicio.
- Tabela auxiliar: `user_hidden_workouts`.

Fluxo atual:

1. Usuario cria um treino em `POST /workouts`.
2. Usuario cria dias em `POST /workouts/{workout}/days`.
3. Usuario adiciona exercicios em `POST /workout-days/{workoutDay}/exercises`.
4. Usuario adiciona series planejadas em `POST /workout-exercises/{workoutExercise}/sets`.
5. Listagem/detalhe de treino carregam dias, exercicios e series.

### Registros de Treino

Existem migrations:

- `workout_logs`
- `workout_log_exercises`

Mas nao existem:

- Models `WorkoutLog` / `WorkoutLogExercise`.
- Controllers.
- Rotas.
- Validacoes.
- Policies.
- Testes.

Conclusao: o banco antecipa o conceito de execucao/registro de treino, mas a API ainda nao expoe esse fluxo para o front-end.

## Models

| Model | Papel | Relacionamentos principais | Observacoes |
|---|---|---|---|
| `User` | Usuario autenticado | tokens Sanctum | Oculta `password` e `remember_token` |
| `MuscleGroup` | Grupo muscular | hasMany `Exercise` | `visibleTo`; falta `owner_user_id` em `$fillable` |
| `Exercise` | Exercicio do catalogo | belongsTo `MuscleGroup` | `visibleTo`; falta `owner_user_id` em `$fillable` |
| `Workout` | Treino/modelo | hasMany `WorkoutDay`; belongsTo owner | `owner_user_id` em `$fillable` |
| `WorkoutDay` | Dia do treino | belongsTo `Workout`; hasMany `WorkoutExercise` | Ordenado por `sort_order` |
| `WorkoutExercise` | Exercicio em um dia | belongsTo `WorkoutDay`, `Exercise`; hasMany `WorkoutExerciseSet` | Usa configuracao de series/reps/descanso |
| `WorkoutExerciseSet` | Serie planejada | belongsTo `WorkoutExercise` | `set_number` unico por exercicio planejado |

## Controllers

| Controller | Responsabilidade | Observacoes |
|---|---|---|
| `AuthController` | login, registro, me, logout | Captura erros de validacao manualmente |
| `MuscleGroupController` | CRUD parcial + hide/unhide | Nao implementa `show`, mas rota existe |
| `ExerciseController` | CRUD parcial + filtros + hide/unhide | Nao implementa `show`, mas rota existe |
| `WorkoutController` | CRUD, hide/unhide, clone | `clone` nao chama `authorize('view')` |
| `WorkoutDayController` | criar/editar/remover dias | Usa policy via workout pai |
| `WorkoutExerciseController` | adicionar/editar/remover exercicios do dia | Valida visibilidade do exercicio, mas nao verifica se exercicio global esta oculto |
| `WorkoutExerciseSetController` | adicionar/editar/remover series | Reordena `set_number` ao remover |

## Policies e Ownership

Policies existentes:

- `OwnedCatalogPolicy`
- `WorkoutPolicy`
- `WorkoutDayPolicy`
- `WorkoutExercisePolicy`
- `WorkoutExerciseSetPolicy`

Regras atuais:

- Treinos globais podem ser visualizados, mas so o dono pode editar/excluir.
- Dias, exercicios do dia e series verificam ownership subindo ate o `Workout`.
- Catalogo deveria usar `OwnedCatalogPolicy`, mas essa policy nao esta ligada explicitamente a `MuscleGroup` e `Exercise`.

Risco pratico:

- `MuscleGroupController::update/destroy` e `ExerciseController::update/destroy` chamam `$this->authorize(...)`.
- Como a policy esta nomeada genericamente e `AppServiceProvider` nao registra `Gate::policy(...)`, a autorizacao pode nao funcionar para esses models.

## Migrations e Banco

Tabelas principais:

- `users`
- `personal_access_tokens`
- `muscle_groups`
- `exercises`
- `workouts`
- `workout_days`
- `workout_exercises`
- `workout_exercise_sets`
- `workout_logs`
- `workout_log_exercises`
- `user_hidden_muscle_groups`
- `user_hidden_exercises`
- `user_hidden_workouts`
- `user_workout_programs`

Coerencia geral:

- O desenho atual de treino por `Workout -> WorkoutDay -> WorkoutExercise -> WorkoutExerciseSet` e coerente para montar um programa de treino.
- `workout_logs` e `workout_log_exercises` sao coerentes com registro de execucao, mas ainda nao integrados ao codigo.
- `user_workout_programs` sugere selecao/ordenacao de treinos ativos do usuario, mas ainda nao ha API.

Problemas de alinhamento:

- `WorkoutSeeder` ainda cria `WorkoutExercise` com `workout_id`, mas a estrutura atual usa `workout_day_id`.
- A migration `2026_03_28_234613_update_workout_exercises_table.php` altera `workout_exercises` para remover `workout_id`, mas a migration original criou unique em `['workout_id', 'exercise_id']`; esse indice nao e tratado explicitamente antes da remocao.
- A migration de refactor de `workouts` remove `user_id` e depois tenta remover unique indexes que ainda referenciam `user_id`, o que pode falhar dependendo do banco.
- Os testes nao rodaram ate o fim no ambiente atual porque falta o driver SQLite/PDO.

## Seeders

Seeders existentes:

- `DatabaseSeeder`
- `MuscleGroupSeeder`
- `ExerciseSeeder`
- `WorkoutSeeder`

Estado:

- `MuscleGroupSeeder` popula grupos globais.
- `ExerciseSeeder` popula exercicios globais.
- `WorkoutSeeder` esta desatualizado com o schema atual de dias de treino.

## Rotas e Endpoints

Todas as rotas abaixo usam prefixo `/api/v1`.

Formato padrao pretendido de sucesso:

```json
{
  "success": true,
  "data": {},
  "message": "Operacao realizada com sucesso"
}
```

Formato padrao pretendido de erro:

```json
{
  "success": false,
  "message": "Erro de validacao",
  "errors": {}
}
```

### Autenticacao

| Metodo | Rota | Auth | Payload | Resposta |
|---|---|---:|---|---|
| POST | `/register` | Nao | `name`, `email`, `password` | `201`, token bearer e usuario |
| POST | `/login` | Nao | `email`, `password` | `200`, token bearer e usuario |
| GET | `/me` | Sim | - | Usuario autenticado |
| POST | `/logout` | Sim | - | Sucesso com `data: {}` |

Validacoes:

- `register`: `name required string max:255`, `email required email unique`, `password required min:6`.
- `login`: `email required email`, `password required`.

### Muscle Groups

| Metodo | Rota | Auth | Payload/Query | Resposta |
|---|---|---:|---|---|
| GET | `/muscle-groups` | Sim | - | Lista visivel e ativa |
| POST | `/muscle-groups` | Sim | `name`, `sort_order?` | `201`, grupo criado |
| GET | `/muscle-groups/{muscle_group}` | Sim | - | Rota existe, mas metodo `show` nao existe |
| PUT/PATCH | `/muscle-groups/{muscle_group}` | Sim | `name?`, `sort_order?`, `is_active?` | Grupo atualizado |
| DELETE | `/muscle-groups/{muscle_group}` | Sim | - | Soft delete via `is_active=false` |
| POST | `/muscle-groups/{muscleGroup}/hide` | Sim | - | Oculta grupo global |
| DELETE | `/muscle-groups/{muscleGroup}/hide` | Sim | - | Reexibe grupo |

Observacoes:

- Criacao tenta atribuir `owner_user_id = user.id`, mas o model nao permite mass assignment desse campo.
- Delete nao remove fisicamente, apenas desativa.
- Hide so permite itens globais.

### Exercises

| Metodo | Rota | Auth | Payload/Query | Resposta |
|---|---|---:|---|---|
| GET | `/exercises` | Sim | `muscle_group_id?`, `equipment?` | Lista visivel e ativa |
| POST | `/exercises` | Sim | `name`, `muscle_group_id`, `equipment?`, `level?`, `instructions?`, `video_url?`, `sort_order?` | `201`, exercicio criado |
| GET | `/exercises/{exercise}` | Sim | - | Rota existe, mas metodo `show` nao existe |
| PUT/PATCH | `/exercises/{exercise}` | Sim | `name?`, `muscle_group_id?`, `equipment?`, `level?`, `instructions?`, `video_url?`, `sort_order?`, `is_active?` | Exercicio atualizado |
| DELETE | `/exercises/{exercise}` | Sim | - | Soft delete via `is_active=false` |
| POST | `/exercises/{exercise}/hide` | Sim | - | Oculta exercicio global |
| DELETE | `/exercises/{exercise}/hide` | Sim | - | Reexibe exercicio |

Validacoes relevantes:

- `level`: inteiro entre 1 e 5.
- `video_url`: URL, max 2048.
- `muscle_group_id`: precisa existir e ser visivel ao usuario.

Ponto de atencao:

- Ao adicionar exercicio a um treino, a API verifica `visibleTo`, mas nao verifica se o exercicio global esta oculto pelo usuario.

### Workouts

| Metodo | Rota | Auth | Payload | Resposta |
|---|---|---:|---|---|
| GET | `/workouts` | Sim | - | Lista visivel, ativa, com dias/exercicios/series |
| POST | `/workouts` | Sim | `name`, `description?`, `sort_order?` | `201`, treino criado |
| GET | `/workouts/{workout}` | Sim | - | Detalhe com dias/exercicios/series |
| PUT/PATCH | `/workouts/{workout}` | Sim | `name?`, `description?`, `sort_order?`, `is_active?` | Treino atualizado |
| DELETE | `/workouts/{workout}` | Sim | - | Soft delete via `is_active=false` |
| POST | `/workouts/{workout}/hide` | Sim | - | Oculta treino global |
| DELETE | `/workouts/{workout}/hide` | Sim | - | Reexibe treino |
| POST | `/workouts/{workout}/clone` | Sim | - | `201`, copia treino com dias/exercicios/series |

Observacoes:

- `show`, `update` e `delete` usam `WorkoutPolicy`.
- `clone` nao chama `authorize('view', $workout)`; como a rota recebe qualquer `Workout` por route model binding, um usuario pode clonar treino privado de outro usuario se souber o ID.

### Workout Days

| Metodo | Rota | Auth | Payload | Resposta |
|---|---|---:|---|---|
| POST | `/workouts/{workout}/days` | Sim | `name`, `sort_order?` | `201`, dia criado |
| PUT | `/workout-days/{workoutDay}` | Sim | `name?`, `sort_order?` | Dia atualizado |
| DELETE | `/workout-days/{workoutDay}` | Sim | - | Dia removido fisicamente |

Observacoes:

- Criar dia exige autorizacao de update no treino.
- Remocao e hard delete; cascata remove exercicios/series vinculados.

### Workout Exercises

| Metodo | Rota | Auth | Payload | Resposta |
|---|---|---:|---|---|
| POST | `/workout-days/{workoutDay}/exercises` | Sim | `exercise_id`, `target_sets?`, `min_reps?`, `max_reps?`, `rest_seconds?`, `notes?`, `sort_order?` | `201`, exercicio do dia criado |
| PUT | `/workout-exercises/{workoutExercise}` | Sim | `target_sets`, `min_reps?`, `max_reps?`, `rest_seconds?`, `notes?`, `sort_order?` | Exercicio do dia atualizado com `exercise` e `sets` |
| DELETE | `/workout-exercises/{workoutExercise}` | Sim | - | Remove exercicio do dia |

Validacoes:

- `target_sets`: `nullable integer min:1` na criacao, mas `required integer min:1` no update quando enviado.
- `min_reps`, `max_reps`: inteiros min 1.
- `rest_seconds`: inteiro min 0.

Ponto de atencao:

- Nao ha regra garantindo `min_reps <= max_reps`.
- A migration original tinha unique para evitar exercicio duplicado por treino, mas o schema atual por dia nao expressa claramente se duplicatas sao permitidas.

### Workout Exercise Sets

| Metodo | Rota | Auth | Payload | Resposta |
|---|---|---:|---|---|
| POST | `/workout-exercises/{workoutExercise}/sets` | Sim | `reps?`, `weight?`, `rest_seconds?`, `rir?`, `notes?` | `201`, serie criada |
| PUT | `/workout-exercise-sets/{workoutExerciseSet}` | Sim | `reps?`, `weight?`, `rest_seconds?`, `rir?`, `notes?` | Serie atualizada |
| DELETE | `/workout-exercise-sets/{workoutExerciseSet}` | Sim | - | Serie removida e numeros seguintes reordenados |

Validacoes:

- `reps`: inteiro min 1.
- `weight`: numerico min 0.
- `rest_seconds`: inteiro min 0.
- `rir`: inteiro entre 0 e 10.

Observacoes:

- `set_number` e calculado automaticamente como `max(set_number) + 1`.
- Ao remover uma serie intermediaria, a API decrementa as series seguintes.

## Consistencia do Padrao de Resposta

O helper `ApiResponse` padroniza sucesso e erro, mas o uso ainda nao cobre todos os cenarios.

Consistente:

- Respostas de sucesso dos controllers principais usam `ApiResponse::success`.
- Alguns erros de negocio usam `ApiResponse::error`.
- `AuthController` captura `ValidationException` manualmente e retorna `success: false`.

Inconsistente:

- Controllers de catalogo/treino usam `$request->validate()` diretamente. Em erro de validacao, Laravel retorna o formato padrao:

```json
{
  "message": "The given data was invalid.",
  "errors": {}
}
```

- Erros de autorizacao, 404 de route model binding e metodos ausentes tambem nao passam por `ApiResponse`.
- `ApiResponse::success(null)` retorna `data: {}`, o que e previsivel, mas deve ser combinado com o front-end.

Recomendacao: centralizar o tratamento de `ValidationException`, `AuthorizationException`, `ModelNotFoundException` e erros gerais em `bootstrap/app.php` ou usar `FormRequest` + exception rendering para manter contrato unico.

## Avaliacao de Validacoes

Pontos positivos:

- Campos principais tem `required`, tipos e limites basicos.
- Slugs sao gerados no backend.
- Unicidade por owner esta prevista nas regras e indices.
- Catalogo valida visibilidade do grupo muscular ao criar exercicio.
- Series tem limites basicos para reps, peso, descanso e RIR.

Lacunas:

- Falta `confirmed` ou regra de forca minima melhor para senha, se o produto exigir.
- Falta normalizacao de email em cadastro/login.
- Falta `min_reps <= max_reps`.
- Falta validar se recursos globais ocultos podem ser usados ao adicionar exercicio a treino.
- Falta `FormRequest`, o que espalha validacao e dificulta padronizar mensagens.
- `show` inexistente em catalogo deixa contratos incompletos.

## Seguranca e Permissoes

Pontos positivos:

- Rotas sensiveis estao protegidas por `auth:sanctum`.
- Treinos e entidades filhas usam policies baseadas no dono do treino.
- Senha e hashada pelo Laravel/User cast e tambem via `Hash::make` no registro.
- Listagens filtram itens visiveis ao usuario.

Riscos:

- `WorkoutController::clone` nao autoriza `view`; risco de clonar treino privado de outro usuario por ID.
- `MuscleGroup` e `Exercise` sem `owner_user_id` em `$fillable` podem transformar criacoes de usuario em itens globais.
- `OwnedCatalogPolicy` nao esta registrada para `MuscleGroup`/`Exercise`.
- Rotas `show` de catalogo quebradas podem gerar erro 500 em vez de resposta controlada.
- Nao ha configuracao visivel de CORS para front-end separado; isso pode bloquear consumo no navegador.
- Tokens Sanctum nao expiram (`expiration => null`), o que pode ser aceitavel no MVP, mas deve ser decisao explicita.

## Codigo Duplicado, Morto ou Confuso

Duplicacao:

- Geracao de slug unico aparece em `MuscleGroupController`, `ExerciseController` e `WorkoutController`.
- Logica `visibleTo + hiddenIds + is_active` aparece de forma parecida nos catalogos.
- Tratamento manual de validacao existe em `AuthController`, mas nao nos demais controllers.

Codigo/estrutura incompleta:

- Rotas `show` para `MuscleGroup` e `Exercise` sem metodo.
- Migrations de logs/programas sem camada de API.
- `WorkoutSeeder` desatualizado.
- Tests de exemplo do Laravel ainda presentes.

Nomes/responsabilidades:

- `WorkoutExerciseSet` representa serie planejada do treino, nao registro executado. Quando `workout_logs` for implementado, vale diferenciar bem "planned set" vs "logged set".
- `OwnedCatalogPolicy` e boa ideia, mas precisa ser registrada ou substituida por `MuscleGroupPolicy` e `ExercisePolicy`.

## Prioridades de Melhoria

### Obrigatorias antes do front-end

1. Corrigir ownership de catalogo:
   - adicionar `owner_user_id` em `$fillable` de `MuscleGroup` e `Exercise`;
   - garantir que criacoes de usuario nao virem globais.
2. Registrar policies de catalogo:
   - mapear `MuscleGroup::class` e `Exercise::class` para `OwnedCatalogPolicy::class`, ou criar policies especificas.
3. Corrigir rotas `show`:
   - implementar `show` em `MuscleGroupController` e `ExerciseController`, ou remover essas rotas com `except(['show'])`.
4. Proteger `WorkoutController::clone`:
   - chamar `authorize('view', $workout)` antes de clonar.
5. Atualizar `WorkoutSeeder` para a estrutura com `WorkoutDay`.
6. Revisar migrations que alteram `workouts` e `workout_exercises`, garantindo que indices/FKs sejam removidos na ordem correta.
7. Padronizar erros JSON para validacao/autorizacao/404 antes do front-end depender do contrato.
8. Configurar CORS para a origem do front-end separado.

### Importantes, mas nao bloqueantes

1. Criar `FormRequest` por recurso para organizar validacoes.
2. Adicionar testes para auth, catalogo, ownership, clone, dias, exercicios e series.
3. Validar `min_reps <= max_reps`.
4. Decidir se exercicios globais ocultos podem ser usados em treinos.
5. Definir contrato de paginacao/filtros para listagens, principalmente `exercises`.
6. Adicionar factories para models de treino/catalogo.
7. Padronizar hard delete vs soft deactivate nos recursos.

### Melhorias futuras

1. Implementar API de registros de treino (`workout_logs`) e historico de execucao.
2. Implementar API de programa ativo do usuario (`user_workout_programs`).
3. Criar Resources/Transformers para controlar formato de resposta e evitar expor campos internos sem decisao.
4. Adicionar expiracao/rotacao de tokens ou logout global.
5. Melhorar busca de exercicios por texto, grupo muscular, equipamento e nivel.
6. Criar documentacao OpenAPI/Swagger quando o contrato estabilizar.

## Status de Testes

Comando executado:

```bash
php artisan test
```

Resultado:

- `Tests\Unit\ExampleTest`: passou.
- `Tests\Feature\ExampleTest`: passou.
- `Tests\Feature\WorkoutExerciseSetTest`: falhou antes de executar as assercoes por ausencia do driver SQLite/PDO no ambiente (`could not find driver`, banco `:memory:`).

Isso significa que a suite ainda nao valida a API neste ambiente. Antes de integrar front-end, vale corrigir o driver de teste ou configurar outro banco de teste.

## Decisao Sobre Front-end

Pode comecar o front-end agora apenas em modo de prototipo, usando contratos preliminares e esperando ajustes na API.

Nao recomendo iniciar integracao definitiva ainda. Os riscos de ownership, rotas quebradas, clone sem autorizacao e resposta de erro inconsistente provavelmente vao causar retrabalho no front-end e podem expor dados entre usuarios.

Ordem recomendada para os primeiros ajustes:

1. Corrigir ownership/policies de `MuscleGroup` e `Exercise`.
2. Corrigir ou remover `show` desses recursos.
3. Proteger `clone`.
4. Padronizar erros JSON.
5. Ajustar seeders/migrations e conseguir rodar `php artisan test`.
6. So entao fechar o contrato inicial consumido pelo front-end.
