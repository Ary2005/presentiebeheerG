<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentielijst</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            margin: 20px;
        }
        .table-container {
            max-width: 800px;
            margin: auto;
        }
        .present, .absent {
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center">Presentielijst</h2>
    <div class="table-container">
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Klas</th>
                    <th>Aanwezig</th>
                    <th>Afwezig</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Jan Jansen</td>
                    <td>ICT-2025</td>
                    <td><button class="btn btn-success present">✔</button></td>
                    <td><button class="btn btn-danger absent">✖</button></td>
                </tr>
                <tr>
                    <td>Lisa de Vries</td>
                    <td>AV-2024</td>
                    <td><button class="btn btn-success present">✔</button></td>
                    <td><button class="btn btn-danger absent">✖</button></td>
                </tr>
                <tr>
                    <td>Tom Willems</td>
                    <td>INFR-2023</td>
                    <td><button class="btn btn-success present">✔</button></td>
                    <td><button class="btn btn-danger absent">✖</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.querySelectorAll('.present').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('tr').style.backgroundColor = "#d4edda"; // Groen
        });
    });

    document.querySelectorAll('.absent').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('tr').style.backgroundColor = "#f8d7da"; // Rood
        });
    });
</script>

</body>
</html>
