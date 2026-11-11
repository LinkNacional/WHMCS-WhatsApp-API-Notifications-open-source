# AGENTS.md — Guia para agentes neste repositório

Este repositório contém o add-on `lknhooknotification` para WHMCS (notificações via WhatsApp/Chatwoot). Leia este arquivo antes de alterar código. As regras valem para agentes automatizados e para pessoas.

## 1. Fluxo de branches e releases

- `dev` é a branch de integração e sempre antecede `main`.
- PR de feature/fix vai para `dev`; não abra PR direto para `main`.
- Antes de criar branch/PR, sincronize:
  ```bash
  git fetch origin
  git rev-list --left-right --count origin/main...origin/dev
  ```
  O contador da esquerda (commits só na main) deve ser `0`. Se `dev` estiver atrás, faça fast-forward: `git push origin main:dev` (nunca force/reset sem autorização).
- Release: **um único PR `dev -> main`**, com título `vX.Y.Z: <resumo>` (ex.: `v4.6.3: fix login OTP (CreateSsoToken client_id)`).
- Bump de versão e `CHANGELOG.md` no branch que entra na `dev`, antes do PR de release.
- Não faça push direto em `main`. Em `dev`, só com autorização explícita do responsável.

## 2. Versionamento

- Fonte única: `src/modules/addons/lknhooknotification/src/Core/Shared/Infrastructure/Config/ModuleInfo.php` (`VERSION`).
- Fix/security: patch; feature: minor. Siga o histórico do `CHANGELOG.md`.
- Atualize `CHANGELOG.md` com data e resumo.
- O workflow `.github/workflows/generate_release.yml` roda ao mergear PR em `main` e cria um **draft release** com `notifications.zip`. Publicar o draft é uma ação separada e aprovada.

## 3. Build e dependências

- `vendor/` não é versionado. O build roda dentro de `src/modules/addons/lknhooknotification`:
  ```bash
  composer install --no-dev --optimize-autoloader
  ```
- O `composer.json` do addon usa PSR-4 `Lkn\HookNotification\ -> src` e platform PHP 8.1.
- Produção e staging usam versões diferentes de WHMCS/PHP; valide com o PHP do alvo e evite sintaxe/funções exclusivas de 8.3 sem necessidade.
- Não commite `vendor/`, `composer-cache`, `node_modules`, dumps, secrets ou arquivos de ambiente.

## 4. Estrutura

- `lknhooknotification.php`: entry point do addon (config, activate, output).
- `hooks.php`: carrega `src/Core/entrypoint.php` e `src/Core/WHMCS/entrypoint.php`.
- `src/Core/AdminUI`: rotas, controllers e views Smarty.
- `src/Core/Notification`: domínio, serviços, listeners e repositórios.
- `src/Core/Platforms`: Baileys, Meta WhatsApp, Evolution e Chatwoot.
- `src/Core/Shared`: `Config/Settings.php`, `Config/Platforms.php`, `ModuleInfo.php`, helpers e repositórios base.
- `tests/`: testes standalone; não entram no zip de release.

## 5. Convenções de código

- PHP 8.1+, namespace `Lkn\HookNotification\...`, PSR-4.
- Use os enums de `Config/Settings.php` e `Config/Platforms.php`; não invente strings soltas.
- Settings: `lkn_hn_config()` / `lkn_hn_config_set()`.
- Logs: `lkn_hn_log()`, com máscaras para segredos (`lkn_hn_mask_value`). Nunca logue token, API key, CVV ou telefone completo.
- Em arquivos com namespace, use `catch (\Throwable $e)`; `catch (Exception)` resolve para o namespace e não captura `TypeError`.
- SQL via Capsule (ou o equivalente do host); nunca concatene input em query.
- Templates Smarty: escape de saída e valide dados de entrada.
- Evite `array_merge($_POST, $_GET)` em rotas que alteram estado.

## 6. Segurança (admin)

- Rotas admin que alteram estado exigem **POST**, token CSRF de sessão e checagem de mesma origem.
- Use o campo dedicado `lkn_hn_token`; o WHMCS injeta seu próprio `token` nos forms e nomes iguais causam ambiguidade.
- Não use `$_REQUEST` para roteamento; prefira `$_GET`/`$_POST` explícitos.
- Valide permissão de admin quando a ação for restrita.
- Segredos ficam no DB/settings; nunca no repo, em logs ou em respostas.
- Teste CSRF em homolog com valores **fake**; nunca use credenciais de produção em homolog.

## 7. Testes e verificação

- `php -l` em todos os `.php` alterados, com o PHP do alvo.
- Testes standalone existentes:
  ```bash
  php tests/param_funcs_module_client_test.php
  ```
- Não há suíte PHPUnit completa; testes de integração exigem WHMCS.
- Mudanças em rotas admin/UI: validar em homolog com backup e rollback.
- Nunca rode migration/SQL destrutivo em produção sem backup e aprovação.

## 8. Deploy

- Deploy em produção/staging exige aprovação explícita do responsável.
- Antes: backup do diretório do módulo e das settings (`tbladdonmodules` + `mod_lkn_hook_notification_configs`).
- Troque arquivos com o módulo fora do docroot em backup; preserve `vendor/`; ajuste dono/permissões.
- Depois: `php -l`, autoload, schema e rota admin (302/200), error log.
- Tenha o rollback pronto (restaurar diretório + versão/settings).

## 9. Git/PR

- Mensagens claras, preferencialmente `tipo(escopo): resumo`.
- PR para `dev` deve descrever problema, mudança, testes e risco/rollback.
- PR de release `dev -> main` com título `vX.Y.Z: ...` e link para issue/advisory.
- Não inclua `vendor/` nem artefatos de build no commit.
- Não faça force-push em branches compartilhadas sem autorização.

## 10. Armadilhas conhecidas

- **Token WHMCS vs token do addon:** use `lkn_hn_token`; o WHMCS injeta `token` nos forms.
- **Settings ausentes:** `SettingsService::updateSettings()` grava `''` para settings não enviadas (semântica de checkbox). Envie o form completo.
- **Dono do serviço:** `getClientIdByModuleId()` usa `tblhosting.id`, não `packageid` (fix #161/#162).
- **Checagem de versão:** throttle de 24h; roda no `DailyCronJob` e, a partir da 4.7.2, também na home do módulo. A UI lê `latest_version`.
- **Logs:** `lkn_hn_log()` respeita `enable_log`; se desligado, não há registro.
- **Smarty:** templates em `src/Core/AdminUI/Http/Views`; variáveis globais via `View`.
- **WHMCS:** hooks e APIs variam entre 8.x e 9.x; valide nos dois quando possível.

## 11. Regras para agentes

- Leia este arquivo e o `README.md` antes de editar.
- Verifique HEAD e data; não confie em memória antiga.
- Não execute ações externas (push, PR, merge, deploy, publish) sem aprovação explícita.
- Prefira mudanças pequenas, com evidência `arquivo:linha`, teste e rollback.
- Se faltar decisão de produto/segurança, pare e pergunte.
