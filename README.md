# Atrações e Experiências PDA

Plugin WordPress para gerenciar o Custom Post Type "Atrações e Experiências" com campos personalizados e widget para Elementor.

## Descrição

Este plugin cria um Custom Post Type chamado "Atrações e Experiências" com diversos campos personalizados para gerenciar informações detalhadas sobre atrações de parques, viveiros, experiências turísticas, etc.

## Funcionalidades

### Custom Post Type

- **Nome:** Atrações e Experiências
- **Slug:** `atracao_experiencia`
- **Taxonomia:** Categorias de Atrações (`atracao_categoria`)

### Campos Personalizados (Meta Boxes)

1. **Informações Principais**
   - Localização
   - Horário de Funcionamento
   - Duração da Visita
   - Nível de Dificuldade

2. **Subtítulo e Descrição Curta**
   - Subtítulo
   - Descrição curta (editor WYSIWYG)

3. **Galeria de Imagens**
   - Múltiplas imagens
   - Ordenável via drag-and-drop

4. **Configurações do Card (Listagem)**
   - Cor de fundo do card
   - Cor do texto do card

5. **Regras e Dicas**
   - Campo repetidor para adicionar múltiplas regras
   - Suporte a ícones Dashicons

6. **Blog Relacionado**
   - Título da seção
   - Descrição
   - Texto e URL do link
   - Imagem da seção

7. **Links Adicionais (Saiba mais)**
   - Campo repetidor para adicionar múltiplos links

### Widget Elementor

- **PDA - Grid de Atrações:** Widget para exibir as atrações em formato de grid com cards
  - Configurações de query (número de itens, categoria, ordenação)
  - Layout responsivo (colunas configuráveis)
  - Estilos personalizáveis (altura do card, cores, tipografia, hover)
  - Usa as cores definidas no post ou cores personalizadas

## Instalação

1. Faça upload da pasta `atracoes-experiencias-pda` para `/wp-content/plugins/`
2. Ative o plugin através do menu 'Plugins' no WordPress
3. Um novo menu "Atrações" aparecerá no admin

## Uso

### Criando uma Atração

1. Vá em **Atrações > Adicionar Nova**
2. Preencha o título e conteúdo principal
3. Adicione a imagem destacada (será usada no card)
4. Preencha os campos personalizados nas meta boxes
5. Defina as cores do card para a listagem
6. Publique

### Usando o Widget no Elementor

1. Edite uma página com Elementor
2. Procure por "Grid de Atrações" ou "PDA"
3. Arraste o widget para a página
4. Configure as opções de query e estilo
5. Salve

## Requisitos

- WordPress 5.0+
- PHP 7.4+
- Elementor 3.0+ (para usar o widget)

## Atualização Automática

O plugin suporta atualização automática via GitHub. Configure o repositório no arquivo principal do plugin.

## Changelog

### 1.0.0
- Versão inicial
- Custom Post Type "Atrações e Experiências"
- Meta boxes completos
- Widget Elementor "Grid de Atrações"
- Suporte a atualização via GitHub

## Autor

**Lui**
- GitHub: [@pereira-lui](https://github.com/pereira-lui)

## Licença

GPL v2 or later
