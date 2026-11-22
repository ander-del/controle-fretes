const formulario = document.getElementById('form-frete');
const listaItens = document.getElementById('itens-frete');
const modelo = document.getElementById('modelo-produto');
const botaoAdicionar = document.getElementById('adicionar-produto');
const origem = document.getElementById('origem_id');
const destino = document.getElementById('destino_id');
const resultado = document.getElementById('resultado-conferencia');

const formatarPeso = (valor) => {
    return valor.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

function atualizarFormulario() {
    let pesoTotal = 0;
    const linhas = listaItens.querySelectorAll('.item-frete');
    const produtosEscolhidos = new Set();

    linhas.forEach((linha) => {
        const produto = linha.querySelector('.produto-select');
        const quantidade = linha.querySelector('.quantidade');
        const opcao = produto.selectedOptions[0];

        const peso = Number(opcao?.dataset.peso || 0);
        const unidades = Number(quantidade.value);

        linha.querySelector('.peso-unitario').value = formatarPeso(peso);

        if (Number.isFinite(unidades) && unidades > 0) {
            pesoTotal += peso * unidades;
        }

        // Evita selecionar o mesmo produto em duas linhas.
        produto.setCustomValidity('');

        if (produto.value && produtosEscolhidos.has(produto.value)) {
            produto.setCustomValidity(
                'Este produto já foi selecionado em outra linha.'
            );
        }

        if (produto.value) {
            produtosEscolhidos.add(produto.value);
        }

        // Mantem pelo menos uma linha de produto.
        linha.querySelector('.remover-produto').disabled =
            linhas.length === 1;
    });

    destino.setCustomValidity(
        origem.value && origem.value === destino.value
            ? 'Escolha um destino diferente da origem.'
            : ''
    );

    document.getElementById('peso-total').textContent =
        formatarPeso(pesoTotal);

    resultado.textContent = '';
    resultado.className = 'mt-3';
}

function adicionarProduto() {
    listaItens.appendChild(modelo.content.cloneNode(true));
    atualizarFormulario();
}

botaoAdicionar.addEventListener('click', adicionarProduto);

listaItens.addEventListener('input', atualizarFormulario);
listaItens.addEventListener('change', atualizarFormulario);

listaItens.addEventListener('click', (evento) => {
    const botao = evento.target.closest('.remover-produto');

    if (botao && listaItens.children.length > 1) {
        botao.closest('.item-frete').remove();
        atualizarFormulario();
    }
});

origem.addEventListener('change', atualizarFormulario);
destino.addEventListener('change', atualizarFormulario);

formulario.addEventListener('submit', (evento) => {
    atualizarFormulario();

    if (!formulario.reportValidity()) {
        evento.preventDefault();
    }
});

// Comeca com uma linha de produto.
adicionarProduto();