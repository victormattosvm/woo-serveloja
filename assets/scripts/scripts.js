// fecha as mensagens de retorno na página de configurações
function Fecha_mensagem(div) {
    setTimeout(function() {
        document.getElementById(div).style.display = "none";
    }, 9000);
}

// controle de paineis
function exibir(div) {
    document.getElementById(div).style.display = "block";
}

function ocultar(div) {
    document.getElementById(div).style.display = "none";
}

function botao_ativo(div) {
    document.getElementById(div).className = "botao-ativo";
}

function botao_inativo(div) {
    document.getElementById(div).className = "botao-inativo";
}

function controle_exibicao(exibir, ocultar, ativo, inativo) {
    this.exibir(exibir);
    this.ocultar(ocultar);
    this.botao_ativo(ativo);
    this.botao_inativo(inativo);
}