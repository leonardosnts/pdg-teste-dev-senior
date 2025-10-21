# Marine Labs – Guia para o Candidato

Bem-vindo à Pelagic Microplastics Observatory. Você foi convidado a analisar a API que alimenta nosso monitoramento oceânico. Este documento apresenta o contexto, arquitetura e os pontos de atenção encontrados durante a auditoria. Seu objetivo é investigar os problemas, propor melhorias e implementar as correções com qualidade profissional. Queremos conhecer o seu raciocínio, decisões técnicas e domínio das ferramentas do Laravel moderno.

## Visão Geral do Domínio

A aplicação representa um laboratório marinho responsável por:

-   Registrar amostras massivas de microplásticos coletadas em expedições.
-   Consolidar relatórios de expedições e suas observações instrumentadas.
-   Manter calibrações de instrumentos sensíveis.
-   Avaliar riscos de deriva salina com base em trajetórias simuladas.
-   Ingerir telemetria acústica e óptica de fauna marcada.
-   Disparar alertas multicanal para equipes de resposta.

Os dados iniciais são semeados via `DatabaseSeeder`, que cria o laboratório `pelagic-lab`, relatórios, observações, surveys de salinidade, calibrações e canais de alerta.

## Arquitetura

-   **Framework**: Laravel.
-   **Controllers**: Mínimos, apenas orquestram Request → Service → Resource → Response. Não há regra de negócio em controllers ou models.
-   **Services**: Camada de domínio responsável por cada fluxo principal (ingestão de amostras, resumos de expedição, etc.).
-   **Repositories**: Interfaces e implementações para dados que exigem encapsulamento adicional (ex.: salinidade).
-   **Resources**: Todos os retornos JSON passam por um Resource específico, garantindo consistência no payload.

## Pontos de Atenção

A auditoria identificou comportamentos problemáticos nos endpoints abaixo. Seu desafio é diagnosticar a causa raiz, desenhar e aplicar a melhoria adequada, e provar por meio de testes que o problema foi sanado.

1. **Ingestão Massiva de Amostras** – `POST /marine-labs/ingest-samples`
    - Expectativa: ingestão de centenas de milhares de registros sem travar o servidor web.
    - Situação atual: requisições síncronas executam toda a carga pesada em linha.
2. **Resumo de Expedições** – `GET /expeditions/summary`
    - Expectativa: múltiplos relatórios e observações carregados de forma eficiente.
    - Situação atual: carga das observações gera impacto proporcional ao volume (N+1).
3. **Registro de Calibrações** – `POST /calibrations`
    - Expectativa: cache refletir sempre a calibração mais recente.
    - Situação atual: valores antigos são mantidos mesmo após novos registros.
4. **Avaliação de Risco Salino** – `POST /salinity/assess`
    - Expectativa: Service container resolvendo dependências necessárias.
    - Situação atual: falta de binding impede execução normal.
5. **Ingestão de Telemetria** – `POST /telemetry/acoustic` e `POST /telemetry/optical`
    - Expectativa: normalização consistente e reutilizável entre tipos de telemetria.
    - Situação atual: lógica quase idêntica aparece duplicada.
6. **Despacho de Alertas** – `POST /alerts/dispatch`
    - Expectativa: arquitetura flexível para suportar novos canais de alerta.
    - Situação atual: canais não previstos são descartados; extensões exigem mudanças na service class.

## Payloads para Exercitar os Problemas

A seguir estão exemplos mínimos para reproduzir cada comportamento. Use-os como ponto de partida; sinta-se livre para ajustar dados e explorar cenários extremos.

### 1. Ingestão Massiva de Amostras

```
POST /marine-labs/ingest-samples
```

```json
{
    "lab_alias": "pelagic-lab",
    "batches": 4,
    "iterations": 200000,
    "particle_count": 1400,
    "depth_start": 10,
    "temperature": 11.8
}
```

Resultado observado: requisições extremamente lentas ou com timeout.

### 2. Resumo de Expedições

```
GET /expeditions/summary?region=Tristan%20Ridge
```

Resultado observado: número de queries cresce conforme mais relatórios são adicionados.

**Dica**: Você pode adicionar mais relatórios usando o endpoint auxiliar abaixo para tornar o problema mais evidente:

```
POST /expeditions
```

```json
{
    "lab_alias": "pelagic-lab",
    "expedition_code": "XP-2001",
    "region": "Tristan Ridge",
    "anomaly_score": 88,
    "metadata": {
        "sampling_window": "2025-09-15"
    },
    "observations": [
        {
            "instrument": "CTD-X12",
            "summary": "Temperature gradient analysis",
            "sample_count": 15
        },
        {
            "instrument": "ADCP-Y44",
            "summary": "Current velocity profiling",
            "sample_count": 12
        },
        {
            "instrument": "LISST-Z89",
            "summary": "Particle concentration scan",
            "sample_count": 18
        }
    ]
}
```

### 3. Registro de Calibrações (Modificar os dados do payload e executar algumas vezes)

```
POST /calibrations
```

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

Resultado observado: dados retornados permanecem ligados à primeira calibração registrada.

### 4. Avaliação de Risco Salino

```
POST /salinity/assess
```

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

Resultado observado: erro 500 relatando dependência não resolvida.

### 5. Telemetria Acústica e Óptica

```
POST /telemetry/acoustic
```

```json
{
    "lab_alias": "pelagic-lab",
    "beacon_id": "AC-5541",
    "signal_strength": -119.27,
    "battery_percent": 47.3,
    "captured_at": "2025-10-20T18:31:00Z"
}
```

```
POST /telemetry/optical
```

```json
{
    "lab_alias": "pelagic-lab",
    "camera_id": "OP-8821",
    "clarity_index": 2.71828,
    "battery_percent": 47.3,
    "captured_at": "2025-10-20T18:31:00Z"
}
```

Resultado observado: pequenas divergências nas regras de normalização entre serviços.

### 6. Despacho de Alertas

```
POST /alerts/dispatch
```

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

Resultado observado: canais não mapeados caem em fallback e são descartados.

## Expectativas de Entrega

-   Apresente um plano de ataque para cada problema antes de alterar o código.
-   Priorize soluções que respeitem a arquitetura existente (Services, Resources, Providers, Jobs, etc.).
-   Garanta cobertura de testes (unit e/ou feature) para validar correções e evitar regressões.
-   Documente decisões relevantes em commits ou notes.

Boa sorte! Estamos interessados em ver sua análise crítica, domínio do ecossistema Laravel e atenção a detalhes.
