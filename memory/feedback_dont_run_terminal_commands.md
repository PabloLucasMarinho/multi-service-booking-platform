---
name: Não rodar comandos no terminal sem permissão
description: O usuário prefere rodar os comandos no terminal ele mesmo e reportar os resultados
type: feedback
---

Não execute comandos no terminal (como `php artisan test`, `php artisan migrate`, etc.) sem que o usuário peça explicitamente. Sugira o comando e deixe que o usuário rode e reporte os resultados.

**Why:** O usuário preferiu rodar os testes ele mesmo.

**How to apply:** Quando precisar rodar um comando, escreva o comando como sugestão em um bloco de código e aguarde o usuário reportar o output.
