# GitHub Actions Workflows

Este diretório contém os workflows do GitHub Actions para automação de testes do **laravel-runware**.

## Workflow: `tests.yml`

Executa os testes do pacote Laravel Runware em múltiplas versões do PHP e Laravel.

### Matriz de Testes

O workflow testa as seguintes combinações:

| PHP Version | Laravel Version |
|-------------|----------------|
| 8.2         | ^11.0          |
| 8.3         | ^11.0          |
| 8.3         | ^12.0          |

**Nota:** Laravel 12 requer PHP 8.3+, por isso PHP 8.2 é excluído dessa combinação.

### Características

- ✅ Validação de composer.json e composer.lock
- ✅ Cache de dependências do Composer
- ✅ Testes em múltiplas versões do PHP e Laravel
- ✅ Execução paralela de jobs para builds mais rápidos
- ✅ Geração de resumo de testes

### Triggers

O workflow é executado automaticamente quando:
- Push para branches `main` ou `develop`
- Pull requests para `main` ou `develop`
- Execução manual via `workflow_dispatch`

### Passos do Workflow

1. **Checkout code** - Baixa o código do repositório
2. **Setup PHP** - Configura a versão do PHP e extensões necessárias
3. **Validate composer** - Valida os arquivos composer
4. **Cache dependencies** - Cacheia dependências para builds mais rápidos
5. **Install dependencies** - Instala dependências do Composer
6. **Run tests** - Executa os testes com PHPUnit

## Requisitos

- PHP 8.2 ou 8.3
- Composer
- Extensões PHP: json, mbstring
- Laravel 11.x ou 12.x

## Como Executar Localmente

Para executar os testes localmente, use:

```bash
# Instalar dependências
composer install

# Executar testes
vendor/bin/phpunit tests/
```

## Troubleshooting

### Erro: "Invalid API key"
Os testes usam mocks e não devem fazer chamadas reais à API. Se você ver este erro, verifique se os mocks estão configurados corretamente.

### Erro: "Class not found"
Certifique-se de que todas as dependências estão instaladas:
```bash
composer install
```

