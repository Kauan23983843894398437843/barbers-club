document.getElementById("previewForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form);

  document.getElementById("result").innerHTML = "<p>Gerando prévia...</p>";

  try {
    const response = await fetch("/api/preview", {
      method: "POST",
      body: formData
    });

    const data = await response.json();

    if (data.error) {
      document.getElementById("result").innerHTML =
        `<p style="color:red">Erro: ${data.error}</p>`;
    } else {
      document.getElementById("result").innerHTML =
        `<img src="${data.url}" alt="Prévia de corte">`;
    }
  } catch (err) {
    document.getElementById("result").innerHTML =
      `<p style="color:red">${err.message}</p>`;
  }
});
