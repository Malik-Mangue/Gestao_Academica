import {
  obrigatorio, dataValida, dataNaoFutura, validarCampos, limparErro
} from "./validacao.js";

// Regras por nome do campo (atributo name do HTML)
const regrasPorCampo = {
  idQualiNivel: [obrigatorio],
  data:         [obrigatorio, dataValida, dataNaoFutura],
  codFormando:  [obrigatorio],
};

const formularios = document.querySelectorAll('form[method="post"]');

for (const formulario of formularios) {
  // Lista de [campo, regras] que existem neste formulário
  const campos = Object.entries(regrasPorCampo)
    .map(([nome, regras]) => [formulario.elements[nome], regras])
    .filter(([campo]) => campo);

  // O formulário de eliminar não tem estes campos: ignorar
  if (campos.length === 0) continue;

  // Validar quando o utilizador sai do campo
  for (const [campo, regras] of campos) {
    campo.addEventListener("blur", () => validarCampos([[campo, regras]]));
  }

  // Validar ao submeter
  formulario.addEventListener("submit", (evento) => {
    const botao = evento.submitter;

    // "Carregar", "Carregar turmas" e "Pesquisar" também fazem submit.
    // Só validamos quando é Gravar ou Editar.
    if (!botao || !["gravar", "editar"].includes(botao.name)) return;

    if (!validarCampos(campos)) evento.preventDefault();
  });

  // O botão "Limpar" também apaga as mensagens de erro
  formulario.addEventListener("reset", () => {
    for (const [campo] of campos) limparErro(campo);
  });
}

