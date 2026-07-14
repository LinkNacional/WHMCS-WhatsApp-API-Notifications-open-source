# 4.4.0 - 14/07/26
- Correção e definição de licença GPL

# 4.3.5 - 27/08/25

- Correção na busca de clientes pela id do orçamento.

# 4.3.4 - 26/08/25

- Correção nos logs de notificações de orçamento.

# 4.3.3 - 16/07/25

- Atualização da documentação.

# 4.3.2 - 14/07/25

- Correção em action.

# 4.3.1 - 30/06/25

- Correção em criação de tabela

# 4.3.0 - 11/06/25

- Suporte ao Meta WhatsApp em mensagens bulk
- Página de logs
- Melhorias na UI

# 4.2.3 - 06/06/25

- Ajustar lógica para identificar número de telefone na resposta do ticket

# 4.2.2 - 06/06/25

- Ajustar erro na listagem de plataformas do bulk

# 4.2.1 - 05/06/25

- Ajustar problemas de cache no dashboard
- Ajustar tratemento de clientes não registrados

# 4.2.0 - 29/05/25

- Possibilidade de tradução de notificações customizadas
- Notificação SafePasswordReset
- Melhorias no tratamento de erros

# 4.1.3 - 21/05/25

- Corrigir tratamento de message template

# 4.1.2 - 19/05/25

- Ajustes na interface
- Ajustes na feature de bulk messaging
- Reimplementação dos custom attributes do Chatwoot Live Chat

# 4.1.1 - 02/05/25

- Remover brealine de parâmetro para compatibilidade com nova regra da API da Meta

# 4.1.0 - 30/04/25

- Correção de bugs de banco de dados

# 4.0.0 - 29/04/25

- Melhorias gerais

# 3.9.1 - 22/04/2025

- #162 - Corrigir script de criação das tabelas do BD.

# 3.9.0 - 28/03/2025

- #162 - Integração com Baileys

# 3.8.1 - 24/03/2025

- #163

# 3.8.0 - 03/03/2025

- #139 WhatsApp Evolution.

# 3.7.1 - 21/02/2025

- #155 Corrigir erro de language ao enviar mensagem.
- #157 Corrigir inputs do tipo number para aceitar números maiores.
- #156 Adicionar fallback para inglês nas línguas do WhatsApp.

# 3.7.0 - 22/01/2025

- Adição de multi-idiomas para notificações da implementação do WhatsApp Meta.
- Adicionar lib FPDI para possibilitar edição de PDFs de faturas.
- Exibir warning quando ambiente não é compatível com requisitos do módulo.

# 3.6.0 - 21/11/2024

- Atualização da versão do Whatsapp API;
- Menu de configurações atualizado para exibir a versão atual da API utilizada pelo módulo;
- Adição de compatibilidade com PHP 8.1.

# 3.5.1 - 05/11/2024

- Correção de erro ao enviar notificações com PDF do Whatasapp.

# 3.5.0 - 28/10/2024

- Correção de erro ao enviar notificações manuais do Whatasapp.

# 3.4.8 - 18/09/2024

- Correção de referência para scripts JS.

# 3.4.7 - 18/09/2024

- Correção de referência dos artefatos carregáveis do módulo para reconhecer quando o WHMCS se encontra em subdiretório.

# 3.4.6 - 12/08/2024

- Correção de erro de build SQL.

# 3.4.5 - 09/08/2024

- Melhoria de logs e tratamento de erros;
- Remoção de chave estrangeira para evitar erros de setup no WHMCS;
- Adição de tratamento para casos de clientes sem custom fiel de número de whatsapp;
- Melhoria no tratamento de casos de clientes não existentes.

# 3.4.4 - 01/07/2024

- Correção de problema no banco de dados;
- Correção de não reconhecimento de templates de idiomas diferentes;
- Adição de configuração de templates.

# 3.4.3 - 20/03/2024

- Correção de problemas com criação de tabelas no banco de dados;
- Correção de problema de página de configurar notificações caso não haja notificações.

# 3.4.2 - 07/03/24

- Correção de instalação de tabela

# 3.4.1 - 30/01/24

- Ajustar lógica para envio de nota privada para clientes não cadastrados

# 3.4.0 - 29/01/24

- #98 Mudança de nomenclatura bate-papo -> integração
- #98 Adcionada descrição para a tela de integração com chatwoot
- #95 Adicionados links para acesso as informações da instância do chatwoot dinamicamente
- #100 Renomeado módulo para WhatsApp e Chatwoot
- Implementar configuração por notificação
- Migração na estrutura em que as configurações ativas do Chatwoot são salvas no banco de dados

# 3.3.0 - 10/11/23

- Remover exclusão das tabelas do banco ao desativar módulo
- Corrigir links para o perfil do cliente colocados no perfil do Chatwoot

# 3.2.1 - 31/08/23

- Correções nas traduções
- Correções na classe Config quando tabela \_config não existe

# 3.2.0 - 31/08/23

- Adicionar suporte a internacionalização do módulo e das notificações
- Adicionar supporte ao Live Chat do Chatwoot
- Melhorar registro de relatórios de envio de notificações
- Ajustar responsividade do módulo em dispositivos mobile
- Destacar botões de como baixar e criar a própria notificação

# 3.1.1 - 04/08/23

- Correções nas verificações envolvendo licença
- Correção na geração de associação entre notificação e message template

# 3.1.0 - 04/08/23

- Adicionar página inicial com documentações e links úteis do módulo
- Adicionar suporte a parâmetros do tipo texto no cabeçalho
- Implementar libphonenumber para validação do telefone do cliente
- Atualizar lógica de licença para aceitar mais de 3 notificações instaladas no plano gratuito
- Adicionar modal para exibir relatório de notificações dentro do perfil do cliente

# 3.0.1 - 27/06/23

- Corrigir bugs de primeira instalação

# 3.0.0 - 21/06/23

- Reimplementar e simplificar criação de notificações
- Suporte a geração de PDF temporário de fatura
- Melhorar configuração de message template com notificação
- Adicionar tela de relatórios
- Simplificar organização do repositório

# 2.3.3 - 08/05/23

- #60 alterar coluna value para longText, por questões de compatibilidade com versões antigas de bancos
- #59 corrigir links para página de logs

# 2.3.2 - 03/05/23

- corrigir erro na página de associação de templete à notificação

# 2.3.1 - 25/04/23

- #53 implementar verificação da existência da tabela mod_paghiper.

# 2.3.0

- adicionar notificação AfterModuleSuspendmigrar configuração para a tela de configurações do Chatwoot (Escutar WhatsApp)
- corrigir select de message templates, adicionando limit=200

# 2.2.1

- Atualizar lógica para criação de hooks customizados

# 2.2.0

- Implentadar verificação de nova versão
- Adicionar logo e descrição na listagem de addons
- Adicionar botão para acesso aos logs do módulo

# 2.1.0

- Correções de errors gramaticais
- Adicionar configuração para definir nome padrão de clientes que não têm os campos de nome preenchidos
- Melhorias no feedback na tela de envio de lembrete de fatura
- Correção de bugs no hook de pedido criado

# 2.0.0

- Adição do Composer e dependências.
- Adição de suporte a criação de hooks customizados.
- Migração e melhorias na tela de configurações.
- Melhorias na tela de registro e edição de message template.
- Adição de uma tela para ajuda de uso

# 1.1.0

- Adicionar arquivos para desenvolvimento com Dev Container
- Adicionar hook "OrderCreated" para WhatsApp
- Adicionar hook "OrderCreated" para canal do WhatsApp no ChatWoot

# 1.0.0

- Painel admin de visualização de fatura
- Enviar lembrete de fatura com apenas texto
- Enviar lembrete da fatura com boleto do PagHiper
- Caso sucesso, envia mensagem semelhante para o Chatwoot como privada
