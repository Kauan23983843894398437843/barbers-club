// Adiciona um listener para o envio do formulário
document.getElementById("cadastroForm").addEventListener("submit", function(event) {
    // Preenche as variáveis com os valores dos campos do formulário
    let usuario = document.getElementById("usuario").value;
    let email = document.getElementById("email").value;
    let senha = document.getElementById("senha").value;
    let senhaError = document.getElementById("senhaError"); // Referência ao elemento de erro da senha

    // Limpar qualquer erro anterior
    if (senhaError) {
        senhaError.remove();
    }

    // Validação simples para garantir que os campos não estão vazios
    if (usuario === "" || email === "" || senha === "") {
        alert("Por favor, preencha todos os campos.");
        event.preventDefault(); // Impede o envio do formulário
        return;
    }

    // Validação de formato de e-mail
    const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    if (!emailPattern.test(email)) {
        alert("Por favor, insira um e-mail válido.");
        event.preventDefault(); // Impede o envio do formulário
        return;
    }

    // Validação de senha (mínimo de 6 caracteres)
    if (senha.length < 6) {
        // Impede o envio do formulário
        event.preventDefault();

        // Cria uma nova mensagem de erro ao lado do campo de senha
        let errorMessage = document.createElement("span");
        errorMessage.id = "senhaError";
        errorMessage.style.color = "red";
        errorMessage.style.fontStyle = "italic";
        errorMessage.textContent = "A senha deve ter pelo menos 6 caracteres.";

        // Adiciona a mensagem de erro ao lado do campo de senha
        let senhaField = document.getElementById("senha").parentNode;
        senhaField.appendChild(errorMessage);

        return;
    }

    // Se todas as validações passarem, o formulário é enviado
});
