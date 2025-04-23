<!DOCTYPE html>
<html lang="ta">
<head>
    <meta charset="UTF-8">
    <title>RinnRaksha Transcript</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            font-size: 14px;
        }

        h1, h2 {
            color: #004085;
        }

        .section {
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
        }

        th {
            background-color: #f0f0f0;
            text-align: left;
        }

        a {
            color: #0056b3;
        }
    </style>
</head>
<body>

    <h1>RinnRaksha Verification Transcript</h1>

    <div class="section">
        <strong>Form No:</strong> {{ $link_details['form_no'] ?? 'N/A' }}<br>
        <strong>Flow Key:</strong> {{ $link_params['flow_key'] ?? 'N/A' }}<br>
        <strong>Loan Type:</strong> {{ $link_params['flow_data']['LOAN_CATEGORY'] ?? 'N/A' }}<br>
        <strong>Completed On:</strong> {{ date('d-m-Y', strtotime($link_details['completed_on'])) ?? '' }}
    </div>

    <div class="section">
        <h2>Media Links</h2>
        <p><strong>Video:</strong> <a href="{{ $prod_Video }}">{{ $prod_Video }}</a></p>
        <p><strong>Sales Brochure:</strong> <a href="{{ $sales_brocher_pdf }}">{{ $sales_brocher_pdf }}</a></p>
        <p><strong>FAQs:</strong> <a href="{{ $faqs_pdf }}">{{ $faqs_pdf }}</a></p>
    </div>

    <div class="section">
        <h2>Screen-wise Audio Text</h2>
        <table>
            <thead>
                <tr>
                    <th>Screen</th>
                    <th>Audio Text</th>
                </tr>
            </thead>
            <tbody>
                @forelse($audio_text as $screen => $text)
                    <tr>
                        <td>{{ ucfirst(str_replace('-', ' ', $screen)) }}</td>
                        <td>{{ $text }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">No transcript data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</body>
</html>
