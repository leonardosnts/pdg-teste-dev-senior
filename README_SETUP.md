# Marine Labs - Setup Técnico e Ambiente de Desenvolvimento

Este guia fornece instruções detalhadas para configurar e executar o projeto utilizando Docker.

## Pré-requisitos

-   **Docker Desktop** instalado e em execução
-   **Git** para clonar o repositório
-   Editor de código (recomendado: VS Code com extensão REST Client para testar a API)

## Estrutura do Projeto

```
pdg-backend/
├── app/               # Código da aplicação Laravel
├── database/          # Migrations, seeders e factories
├── routes/            # Definição de rotas da API
├── tests/             # Testes unitários e de feature
├── docker-compose.yaml # Configuração dos containers
├── Dockerfile         # Imagem da aplicação Laravel
├── api.rest           # Exemplos de requisições HTTP
└── .env               # Variáveis de ambiente (criado automaticamente)
```

## Inicialização Rápida

### 1. Subir o Ambiente

Execute o comando abaixo na raiz do projeto:

```bash
docker compose -f docker-compose.yaml up -d --build
```

Este comando irá:

-   ✅ Construir a imagem Docker da aplicação Laravel
-   ✅ Iniciar os containers: MySQL, Redis e Laravel
-   ✅ Aguardar o MySQL estar completamente pronto
-   ✅ Executar automaticamente `php artisan migrate:fresh --seed`
-   ✅ Popular o banco com dados de teste (laboratório, expedições, etc.)
-   ✅ Iniciar o servidor Laravel na porta `8090`
-   ✅ Iniciar o queue worker em background

**Tempo estimado:** 2-3 minutos na primeira execução.

### 2. Verificar Status dos Containers

```bash
docker compose -f docker-compose.yaml ps
```

Você deve ver 3 containers rodando:

-   `pdg-backend-1` (aplicação Laravel)
-   `pdg-backend-mysql-1` (banco de dados)
-   `pdg-backend-redis-1` (cache e filas)

### 3. Verificar Logs da Aplicação

```bash
docker compose -f docker-compose.yaml logs pdg-backend -f
```

Você deve ver mensagens como:

```
MySQL está pronto!
Executando migrations...
INFO  Running migrations.
INFO  Seeding database.
Iniciando servidor Laravel...
INFO  Server running on [http://0.0.0.0:8090].
```

### 4. Testar a API

A API estará disponível em: **http://localhost:8090**

Teste com um endpoint simples:

```bash
curl http://localhost:8090/api
```

Ou use o arquivo `api.rest` no VS Code (com a extensão REST Client instalada).

## Serviços e Portas

| Serviço | Container   | Porta Host | Porta Interna |
| ------- | ----------- | ---------- | ------------- |
| Laravel | pdg-backend | 8090       | 8090          |
| MySQL   | mysql       | 3308       | 3306          |
| Redis   | redis       | 6381       | 6379          |

## Comandos Úteis

### Parar os Containers

```bash
docker compose -f docker-compose.yaml down
```

### Reconstruir e Reiniciar (após mudanças no código)

```bash
docker compose -f docker-compose.yaml down
docker compose -f docker-compose.yaml up -d --build
```

### Executar Comandos Artisan

```bash
# Listar rotas
docker compose -f docker-compose.yaml exec pdg-backend php artisan route:list

# Limpar cache
docker compose -f docker-compose.yaml exec pdg-backend php artisan cache:clear

# Rodar testes
docker compose -f docker-compose.yaml exec pdg-backend php artisan test

# Acessar tinker
docker compose -f docker-compose.yaml exec pdg-backend php artisan tinker
```

### Acessar o Container

```bash
docker compose -f docker-compose.yaml exec pdg-backend bash
```

### Visualizar Logs em Tempo Real

```bash
# Logs da aplicação
docker compose -f docker-compose.yaml logs pdg-backend -f

# Logs do MySQL
docker compose -f docker-compose.yaml logs mysql -f

# Logs de todos os serviços
docker compose -f docker-compose.yaml logs -f
```

### Re-executar Migrations e Seeds

```bash
docker compose -f docker-compose.yaml exec pdg-backend php artisan migrate:fresh --seed
```

### Acessar o Banco de Dados Diretamente

```bash
docker compose -f docker-compose.yaml exec mysql mysql -uroot -proot pdg
```

## Configuração do Ambiente

### Variáveis de Ambiente

O arquivo `.env` é criado automaticamente na primeira execução baseado em `.env.example`. As principais variáveis já configuradas:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=pdg
DB_USERNAME=root
DB_PASSWORD=root

REDIS_HOST=redis
REDIS_PORT=6379

CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

### Personalizar Configurações

Se precisar alterar variáveis de ambiente:

1. Edite o arquivo `.env` dentro do container ou monte um volume
2. Reinicie os containers:
    ```bash
    docker compose -f pdg-backend/docker-compose.yaml restart pdg-backend
    ```

## Estrutura dos Dados Iniciais

Após o seed, você terá:

-   **1 Marine Lab** (`pelagic-lab`)
-   **3 Expedition Reports** na região "Tristan Ridge"
-   **9 Observations** distribuídas entre os relatórios
-   **2 Salinity Surveys**
-   **1 Instrument Calibration**
-   **3 Alert Channels** (email, sms, webhook)

## Testando os Endpoints com Problemas

Use o arquivo `api.rest` incluído no projeto. Ele contém exemplos de requisições para todos os 6 problemas identificados.

### Com VS Code + REST Client Extension

1. Instale a extensão "REST Client" no VS Code
2. Abra o arquivo `api.rest`
3. Clique em "Send Request" acima de cada requisição

### Com cURL

Exemplos disponíveis no README.md principal do projeto.

## Troubleshooting

### Container não inicia ou fica reiniciando

```bash
# Verificar logs detalhados
docker compose -f docker-compose.yaml logs pdg-backend

# Reconstruir do zero
docker compose -f docker-compose.yaml down -v
docker compose -f docker-compose.yaml up -d --build
```

### Erro "Connection refused" ao conectar no MySQL

Aguarde alguns segundos. O MySQL leva ~10-15 segundos para estar completamente pronto. O entrypoint já aguarda automaticamente.

### Porta 8090 já está em uso

Mude a porta no `docker-compose.yaml`:

```yaml
ports:
    - "8091:8090" # Use 8091 no host
```

### Migrations não foram executadas

Execute manualmente:

```bash
docker compose -f docker-compose.yaml exec pdg-backend php artisan migrate:fresh --seed --force
```

### Queue não está processando jobs

Verifique se o worker está rodando:

```bash
docker compose -f docker-compose.yaml exec pdg-backend ps aux | grep queue
```

Se não estiver, reinicie o container:

```bash
docker compose -f docker-compose.yaml restart pdg-backend
```

## Desenvolvimento e Testes

### Executar Testes

```bash
# Todos os testes
docker compose -f docker-compose.yaml exec pdg-backend php artisan test

# Com coverage (se PHPUnit estiver configurado)
docker compose -f docker-compose.yaml exec pdg-backend php artisan test --coverage

# Testes específicos
docker compose -f docker-compose.yaml exec pdg-backend php artisan test --filter=ExpeditionTest
```

### Debug com Logs

Os logs da aplicação ficam em `storage/logs/laravel.log`. Para visualizar:

```bash
docker compose -f docker-compose.yaml exec pdg-backend tail -f storage/logs/laravel.log
```

## Limpeza Completa

Para remover tudo (containers, volumes, redes):

```bash
docker compose -f docker-compose.yaml down -v --remove-orphans
docker system prune -a
```

⚠️ **Atenção:** Isso apagará todos os dados do banco de dados!

## Performance e Otimizações

### Cache de Configuração

Em produção, considere cachear as configurações:

```bash
docker compose -f docker-compose.yaml exec pdg-backend php artisan config:cache
docker compose -f docker-compose.yaml exec pdg-backend php artisan route:cache
```

### Monitorar Uso de Recursos

```bash
docker stats
```

## Próximos Passos

1. ✅ Ambiente está pronto e rodando
2. 📖 Leia o `README.md` para entender os problemas a serem resolvidos
3. 🧪 Execute as requisições no `api.rest` para reproduzir os problemas
4. 🔍 Investigue o código em `app/Services/`, `app/Controllers/`, etc.
5. ✨ Implemente as correções
6. ✅ Valide com testes

## Suporte

Para questões sobre o desafio técnico, consulte o `README.md` principal ou entre em contato com o time técnico.

---

**Boa sorte no desafio! 🚀**
