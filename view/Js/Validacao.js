// ===== FUNÇÕES AUXILIARES (não exportadas) =====
function obterDataDeHoje() {
  const hoje = new Date();
  const ano = hoje.getFullYear();
  const mes = String(hoje.getMonth() + 1).padStart(2, "0");
  const dia = String(hoje.getDate()).padStart(2, "0");
  return `${ano}-${mes}-${dia}`;
}

// ===== REGRAS DE TEXTO =====
export const obrigatorio = (valor) =>
  valor.trim() !== "" ? "" : "Campo obrigatório.";

export const minimo = (tamanhoMinimo) => (valor) =>
  valor.length >= tamanhoMinimo ? "" : `Mínimo de ${tamanhoMinimo} caracteres.`;

export const maximo = (tamanhoMaximo) => (valor) =>
  valor.length <= tamanhoMaximo ? "" : `Máximo de ${tamanhoMaximo} caracteres.`;

// NOVA: tamanho exacto (ex: NUIT com 9 dígitos)
export const tamanhoExacto = (tamanho) => (valor) =>
  valor.length === tamanho ? "" : `Deve ter exactamente ${tamanho} caracteres.`;

// NOVA: sem espaços no início ou no fim
export const semEspacosNasPontas = (valor) =>
  valor === valor.trim() ? "" : "Remova os espaços no início ou no fim.";

// NOVA: apenas letras (aceita acentos, espaços, hífen e apóstrofo)
export const apenasLetras = (valor) =>
  /^[A-Za-zÀ-ÿ\s'-]+$/.test(valor) ? "" : "Use apenas letras.";

// NOVA: regra genérica com expressão regular própria
export const padrao = (expressao, mensagem = "Formato inválido.") => (valor) =>
  expressao.test(valor) ? "" : mensagem;

// ===== REGRAS DE CONTACTO E DOCUMENTOS =====
export const email = (valor) =>
  /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor) ? "" : "Email inválido.";

// Telefone de Moçambique: 82-87 + 7 dígitos, com ou sem +258
export const telefoneMZ = (valor) =>
  /^(\+?258)?8[2-7]\d{7}$/.test(valor) ? "" : "Telefone inválido (ex: 841234567).";

// NOVA: NUIT tem 9 dígitos
export const nuit = (valor) =>
  /^\d{9}$/.test(valor) ? "" : "NUIT deve ter 9 dígitos.";

// NOVA: BI moçambicano tem 12 dígitos + 1 letra (ex: 110100123456B)
export const bilheteIdentidade = (valor) =>
  /^\d{12}[A-Za-z]$/.test(valor) ? "" : "BI inválido (12 dígitos e 1 letra).";

// NOVA: endereço web
export const urlValida = (valor) => {
  try {
    new URL(valor);
    return "";
  } catch {
    return "Endereço web inválido (ex: https://exemplo.com).";
  }
};

// ===== REGRAS DE NÚMEROS =====
export const apenasNumeros = (valor) =>
  /^\d+$/.test(valor) ? "" : "Use apenas números.";

// NOVA: número com ou sem casas decimais (ex: 12 ou 12.50)
export const numeroDecimal = (valor) =>
  /^\d+(\.\d{1,2})?$/.test(valor) ? "" : "Número inválido (ex: 12.50).";

// NOVA: valor mínimo e máximo
export const valorMinimo = (limite) => (valor) =>
  Number(valor) >= limite ? "" : `O valor mínimo é ${limite}.`;

export const valorMaximo = (limite) => (valor) =>
  Number(valor) <= limite ? "" : `O valor máximo é ${limite}.`;

// ===== REGRAS DE DATAS =====
// NOVA: formato AAAA-MM-DD e data que existe no calendário
export const dataValida = (valor) => {
  const formatoCerto = /^\d{4}-\d{2}-\d{2}$/.test(valor);
  const existe = !isNaN(new Date(valor));
  return formatoCerto && existe ? "" : "Indique uma data válida.";
};

// NOVA: não permite datas depois de hoje
export const dataNaoFutura = (valor) =>
  valor <= obterDataDeHoje() ? "" : "A data não pode ser futura.";

// NOVA: não permite datas antes de hoje
export const dataNaoPassada = (valor) =>
  valor >= obterDataDeHoje() ? "" : "A data não pode ser passada.";

// NOVA: data dentro de um intervalo (ex: dataEntre("2020-01-01", "2030-12-31"))
export const dataEntre = (dataInicial, dataFinal) => (valor) =>
  valor >= dataInicial && valor <= dataFinal
    ? ""
    : `A data deve estar entre ${dataInicial} e ${dataFinal}.`;

// NOVA: idade mínima a partir da data de nascimento
export const idadeMinima = (anos) => (valor) => {
  const limite = new Date();
  limite.setFullYear(limite.getFullYear() - anos);
  return new Date(valor) <= limite ? "" : `Idade mínima: ${anos} anos.`;
};

// ===== REGRAS DE SENHA E COMPARAÇÃO =====
// NOVA: senha com maiúscula, minúscula e número
export const senhaForte = (valor) =>
  /[A-Z]/.test(valor) && /[a-z]/.test(valor) && /\d/.test(valor)
    ? ""
    : "A senha deve ter maiúscula, minúscula e número.";

// Compara com outro campo (ex: confirmar senha)
export const igualA = (obterValorDoOutroCampo, mensagem = "Os valores não coincidem.") => (valor) =>
  valor === obterValorDoOutroCampo() ? "" : mensagem;

// NOVA: valor tem de estar numa lista permitida
export const umDe = (listaPermitida, mensagem = "Valor não permitido.") => (valor) =>
  listaPermitida.includes(valor) ? "" : mensagem;

// ===== TORNAR UMA REGRA OPCIONAL =====
// NOVA: se o campo estiver vazio, passa; se tiver texto, aplica a regra
export const opcional = (regra) => (valor) =>
  valor.trim() === "" ? "" : regra(valor);

// ===== MOSTRAR / LIMPAR ERRO NA PÁGINA =====
export function mostrarErro(campo, mensagem) {
  let elementoErro = campo.nextElementSibling;

  if (!elementoErro || !elementoErro.classList.contains("erro")) {
    elementoErro = document.createElement("span");
    elementoErro.className = "erro";
    campo.after(elementoErro);
  }

  elementoErro.textContent = mensagem;
  campo.classList.add("invalido");
}

export function limparErro(campo) {
  const elementoErro = campo.nextElementSibling;

  if (elementoErro && elementoErro.classList.contains("erro")) {
    elementoErro.textContent = "";
  }

  campo.classList.remove("invalido");
}

// ===== VALIDAR UM CAMPO COM UMA LISTA DE REGRAS =====
export function validarCampo(campo, listaDeRegras) {
  for (const regra of listaDeRegras) {
    const mensagemDeErro = regra(campo.value);

    if (mensagemDeErro) {
      mostrarErro(campo, mensagemDeErro);
      return false; // para na primeira falha
    }
  }

  limparErro(campo);
  return true;
}

// Valida vários campos de uma vez (todos mostram o erro, não só o primeiro)
export function validarCampos(listaDeCampos) {
  let tudoValido = true;

  for (const [campo, listaDeRegras] of listaDeCampos) {
    if (!validarCampo(campo, listaDeRegras)) tudoValido = false;
  }

  return tudoValido;
}
