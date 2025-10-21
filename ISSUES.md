# Backlog de Issues – Marine Labs API

---

## Issue 1 · Latência extrema na ingestão de amostras de microplásticos

-   **Endpoint**: `POST /marine-labs/ingest-samples`
-   **Sintoma**: Requisições levam vários minutos e frequentemente resultam em timeout.
-   **Passos para reproduzir**:
    ```pwsh
    docker-compose exec pdg-backend curl \
      -X POST http://localhost:8090/api/marine-labs/ingest-samples \
      -H "Content-Type: application/json" \
      -d '{"lab_alias":"pelagic-lab","batches":4,"iterations":200000,"particle_count":1400,"depth_start":10,"temperature":11.8}'
    ```
-   **Resultado esperado**: Resposta rápida, mantendo o processamento pesado fora do ciclo HTTP.
-   **Critérios de aceitação**:
    -   [ ] Requisições retornam em tempo aceitável.
    -   [ ] Inserções volumosas ocorrem sem bloquear o request.
    -   [ ] Dados permanecem íntegros após processar.
    -   [ ] Sem regressão no payload da resposta.
-   **Testes obrigatórios**:
    -   [ ] Feature test garantindo resposta imediata.
    -   [ ] Teste provando que a tarefa pesada continua em segundo plano.
    -   [ ] Unit test para a classe responsável pela orquestração da ingestão.

---

## Issue 2 · Consultas N+1 no resumo de expedições

-   **Endpoint**: `GET /expeditions/summary?region=Tristan%20Ridge`
-   **Sintoma**: Volume de queries cresce linearmente com a quantidade de relatórios.
-   **Passos para reproduzir**:
    ```pwsh
    docker-compose exec pdg-backend curl "http://localhost:8090/api/expeditions/summary?region=Tristan%20Ridge"
    ```
-   **Resultado esperado**: Carga dos dados realizada em número constante de queries.
-   **Critérios de aceitação**:
    -   [ ] Endpoint executa consultas otimizadas (verificável via query log).
    -   [ ] Tempo de resposta consistente em datasets grandes.
    -   [ ] Estrutura do JSON preservada.
-   **Testes obrigatórios**:
    -   [ ] Feature test validando o payload.
    -   [ ] Teste monitorando contagem de queries.
    -   [ ] Unit test para o serviço que compõe o resumo.

---

## Issue 3 · Calibrações servindo dados obsoletos por cache permanente

-   **Endpoint**: `POST /calibrations`
-   **Sintoma**: Requisições subsequentes retornam valores antigos, ignorando novas calibrações.
-   **Payload de exemplo**:
    ```json
    {
        "marine_lab_id": 1,
        "instrument": "lisst-deep",
        "drift_ppm": 4.911,
        "validated_at": "2025-10-01T12:00:00Z",
        "payload": {
            "operator": "JP-32",
            "notes": "Recalibração após tempestade"
        }
    }
    ```
-   **Resultado esperado**: Cache refletindo sempre a calibração mais recente.
-   **Critérios de aceitação**:
    -   [ ] Respostas subsequentes trazem os dados recém-armazenados.
    -   [ ] Estratégia de cache evita estalos de performance negativos.
    -   [ ] Payload de resposta inalterado.
-   **Testes obrigatórios**:
    -   [ ] Feature test validando atualização após nova calibração.
    -   [ ] Unit test da regra de cache (utilizando store fake).
    -   [ ] Teste confirmando persistência correta do registro.

---

## Issue 4 · Falha de injeção no cálculo de risco salino

-   **Endpoint**: `POST /salinity/assess`
-   **Sintoma**: Erro 500 por ausência de binding para `OceanDriftRepositoryInterface`.
-   **Payload de exemplo**:
    ```json
    {
        "lab_alias": "pelagic-lab",
        "coordinates": [
            { "lat": -37.92, "lng": -11.44 },
            { "lat": -38.1, "lng": -11.6 },
            { "lat": -38.35, "lng": -11.88 }
        ]
    }
    ```
-   **Resultado esperado**: Endpoint responde com avaliação de risco sem erros de container.
-   **Critérios de aceitação**:
    -   [ ] Binding configurado e testado em Service Provider.
    -   [ ] Requisição retorna status 200 com dados calculados.
    -   [ ] Sem regressão no contrato do serviço.
-   **Testes obrigatórios**:
    -   [ ] Feature test para o endpoint.
    -   [ ] Unit test do serviço com mock de repositório.
    -   [ ] Teste assegurando que o Provider é carregado (p.ex. `App::make`).

---

## Issue 5 · Duplicação de lógica nas ingestões de telemetria

-   **Endpoints**: `POST /telemetry/acoustic` e `POST /telemetry/optical`
-   **Sintoma**: Normalização duplicada e divergente entre serviços acústicos e ópticos.
-   **Payloads de exemplo**:
    -   Acústico:
        ```json
        {
            "lab_alias": "pelagic-lab",
            "beacon_id": "AC-5541",
            "signal_strength": -119.27,
            "battery_percent": 47.3,
            "captured_at": "2025-10-20T18:31:00Z"
        }
        ```
    -   Óptico:
        ```json
        {
            "lab_alias": "pelagic-lab",
            "camera_id": "OP-8821",
            "clarity_index": 2.71828,
            "battery_percent": 47.3,
            "captured_at": "2025-10-20T18:31:00Z"
        }
        ```
-   **Resultado esperado**: Uma única fonte de verdade para normalização, utilizada por ambos os fluxos.
-   **Critérios de aceitação**:
    -   [ ] Código compartilhado elimina duplicação e divergências.
    -   [ ] Comportamento consistente entre acústico e óptico.
    -   [ ] Nenhuma regressão na estrutura das respostas.
-   **Testes obrigatórios**:
    -   [ ] Feature tests para cada endpoint.
    -   [ ] Unit test da abstração/trait criada.
    -   [ ] Testes cobrindo casos-limite (bateria fora da faixa, dados ausentes etc.).

---

## Issue 6 · Disparo de alertas rígido sem Adapter

-   **Endpoint**: `POST /alerts/dispatch`
-   **Sintoma**: Canais novos configurados em banco são descartados por falta de extensibilidade.
-   **Payload de exemplo**:
    ```json
    {
        "lab_alias": "pelagic-lab",
        "event_type": "SATELLITE_DRIFT",
        "payload": {
            "summary": "Deriva crítica detectada",
            "details": {
                "sector": "Tristan Ridge",
                "threshold": 3.6
            }
        },
        "triggered_at": "2025-10-20T17:45:00Z"
    }
    ```
-   **Resultado esperado**: Arquitetura extensível permitindo novos canais sem alterar o serviço principal.
-   **Critérios de aceitação**:
    -   [ ] Adição de canal demanda apenas registrar novo Adapter.
    -   [ ] Canais existentes continuam funcionando sem alterações.
    -   [ ] Logs/respostas deixam claro qual Adapter tratou o alerta.
-   **Testes obrigatórios**:
    -   [ ] Feature test cobrindo canais padrão.
    -   [ ] Teste com Adapter fake para canal customizado.
    -   [ ] Unit tests para cada Adapter concreto.
