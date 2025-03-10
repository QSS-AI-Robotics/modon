<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Locations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

<div class="container mt-5">
    <h2>Manage Locations</h2>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addLocationModal">Add Location</button>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Region</th> <!-- ✅ Added Region -->
                <th>Map</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($locations as $location)
                <tr data-id="{{ $location->id }}">
                    <td>{{ $location->name }}</td>
                    <td>{{ $location->latitude }}</td>
                    <td>{{ $location->longitude }}</td>
                    <td>{{ $location->region->name }}</td> <!-- ✅ Show Region Name -->
                    <td><a href="{{ $location->map_url }}" target="_blank">View</a></td>
                    <td>{{ $location->description }}</td>
                    <td>
                        <button class="btn btn-warning edit-location" data-id="{{ $location->id }}">Edit</button>
                        <button class="btn btn-danger delete-location" data-id="{{ $location->id }}">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
        
    </table>
</div>

<!-- Modal for Adding/Editing Locations -->
<div class="modal fade" id="addLocationModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Add/Edit Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="locationForm">
                    @csrf
                    <input type="hidden" id="locationId">
                    <input type="text" class="form-control mb-2" id="name" placeholder="Location Name" required>
                    <input type="number" step="any" class="form-control mb-2" id="latitude" placeholder="Latitude" required>
                    <input type="number" step="any" class="form-control mb-2" id="longitude" placeholder="Longitude" required>
                    <input type="url" class="form-control mb-2" id="map_url" placeholder="Google Maps URL">
                    <textarea class="form-control mb-2" id="description" placeholder="Description"></textarea>
                    <button type="submit" class="btn btn-success w-100">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/locations.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
