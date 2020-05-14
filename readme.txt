=== WooCommerce Serveloja ===
Contributors: TiServeloja
Donate link: 
Tags: woocommerce, serveloja, payment
Requires at least: 4.0
Tested up to: 5.2.2
Stable tag: 2.7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adicione o gateway da Serveloja ao plugin WooCommerce.

== Description ==

O Woocommerce Serveloja, fornece aos proprietários de e-commerces (lojas virtuais) uma solução elegante, rápida e responsiva de finalizar suas vendas, utilizando cartões de créditos.

== Installation ==

Para utilizar o plugin, é necessário que seu E-commerce possua o protocolo HTTPS implementado. Além disso você precisará possuir uma conta na [Serveloja](http://www.serveloja.com.br) e ter instalado o [WooCommerce](http://wordpress.org/plugins/woocommerce/).

Depois de baixar e instalar o plugin Woocommerce e o plugin Woocommerce Serveloja, através do instalador de plugins do Wordpress, é necessário ativar os plugins na página de plugins instalados. 

Ainda no admin do WordPress, acesse no menu "Serveloja" > "Configurações" e informe o Nome da Aplicação (ID da Aplicação) e o Token, gerados através da sua conta na Serveloja.

Após isso, acesse "Serveloja" > "Cartões" e informe as bandeiras de cartões com as quais irá receber pagamentos, bem como, a quantidade máxima de parcelas que irá receber para cada bandeira.

Por fim, acesse "Serveloja" > "WooCommerce" e habilite a opção "Utilizar WooCommerce Serveloja para receber pagamentos". Em seguida, escolha o método de integração entre o seu E-commerce e o nosso gateway.

== Frequently Asked Questions ==

= Qualquer pessoa pode utilizar o WooCommerce Serveloja? =

Sim, qualquer pessoa pode utilizar, desde que tenha uma conta habilitada e ativa na Serveloja, e tenha WooCommerce instalado em seu e-commerce (loja virtual).

= Como conseguir o Nome da Aplicação (ID da Aplicação) e o Token para usar em minha loja? =

Você precisa entrar em contato com a Serveloja, realizar seu cadastro e acessar o sistema. A partir daí, você poderá gerar o Nome da Aplicação e Token para que seja possível a utilização da sua loja.

= Meus clientes precisarão realizar algum cadastro no ambiente da Serveloja? =

Não. Os usuários, clientes da sua loja não precisarão realizar nenhum cadastro na Serveloja. Os dados informados no momento do pagamento, são usados para validar a operação e não são gravados no sistema da Serveloja.

= A Serveloja aceita outras formas de pagamento além do cartão de crédito? =

No plugin WooCommerce Serveloja, não. Aqui são aceitos apenas cartões de crédito como forma de pagamento.

= Se não for informado o Nome da Aplicação e o Token, as transações serão realizadas? =

Não. Você poderá realizar a instalação, mas para uso efetivo só será concretizado após a informação de Nome da Aplicação e Token.

= Ao ir para página de finalização de pedido, o botão "Finalizar compra" está desabilitado. É correto? =

Somente após serem fornecidas as informações obrigatórias, o botão estará disponível para finalização do processo.

= Verifiquei que não existem alguns cartões na lista de seleção no pagamento. O que fazer? =

Verifique no admin do WordPress em "Serveloja" > "Cartões" e verifique se a bandeira desejada está marcada como "Sim" e a quantidade de parcelas está correta com a quantidade que você trabalha.

= Após o usuário da loja clicar no botão "Finalizar", ocorre um erro dizendo que a transação não foi liberada. O que aconteceu? =

Se isto ocorrer, será informada uma mensagem de retorno com a descrição do problema. Se o erro foi causado por algum dado informado incorretamente pelo usuário, este deve ser corrigido e o processo deve ser refeito. Caso haja problemas quanto a liberação da operação, o usuário deve entrar em contato com a operadora do cartão para resolvê-los.

= Como entro em contato com a Serveloja? =

Acesse o site www.serveloja.com.br, e você terá acesso a todos os nosso canais de comunicação.

= Como criar uma conta Serveloja? =

Acesse o site www.serveloja.com.br, e você terá todas as informações de como criar sua conta na Serveloja, além de ter a possibilidade de falar com um de nossos representantes.

== Screenshot ==

1. Plugin após a instalação sendo listado e aparecendo no menu do WordPress.
2. Tela de Configuração em Serveloja.
3. Formulário onde serão adicionados informações como Nome da Aplicação e Token para uso em transações.
4. Lista de possíveis cartões para uso em transações.
5. Tela de configuração no Woocommerce.
6. Opção de checkout transparente na tela do cliente, no E-commerce.
7. Aspecto da tela de checkout transparente com todos os dados preenchidos e botão "Finalizar compra" disponível.
8. Validação de formulário automática retornará erros durante o preenchimento, caso haja informações incompátiveis.
9. Opção de lightbox na tela do cliente, no E-commerce.
10. Aspecto da tela de lightbox com todos os dados preenchidos e botão "Finalizar compra" disponível.
11. Modal de opções de confirmação, caso o modal seja fechado.
12. Tela de sucesso da transação do checkout transparente.

== Changelog ==

= 2.7.0 =
* Modificação do fluxo do checkout transparente, agora presente na tela de checkout.
* Melhorias em relação a usabilidade, tanto na loja quando no ambiente administrativo.
* Alteração do fluxo do lightbox, agora ao cancelar a janela de pagamento o pedido não fica mais como pendente.

= 2.1 - 2.6 =
* Adequação dos elementos do plugin às variações dos temas do Wordpress.

= 2.0 =
* Adição de mais uma opção de integração, agora é possível integrar o plugin serveloja via checkout transparente.
* Alterações no layout do plugin.

= 1.0 =
* Versão inicial do plugin.