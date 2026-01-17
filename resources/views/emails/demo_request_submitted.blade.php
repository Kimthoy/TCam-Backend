<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Demo Request</title>
</head>
<body>
    <h2>New Demo Request Submitted</h2>
    <p><strong>Name:</strong> {{ $requestDemo->name }}</p>
    <p><strong>Email:</strong> {{ $requestDemo->email }}</p>
    <p><strong>Company:</strong> {{ $requestDemo->company ?? 'N/A' }}</p>
    <p><strong>Description:</strong></p>
    <p>{{ $requestDemo->description ?? 'N/A' }}</p>
</body>
</html>
