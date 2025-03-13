<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

<div class="container mt-5">
    <h2>Manage Missions</h2>
<!-- Bootstrap Modal for Editing a Mission -->
<div class="modal fade" id="editMissionModal" tabindex="-1" aria-labelledby="editMissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMissionModalLabel">Edit Mission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editMissionForm">
                    @csrf
                    <input type="hidden" id="edit_mission_id" name="mission_id">

                    <!-- Inspection Types -->
                    <div class="mb-3">
                        <label class="form-label">Select Inspection Types</label>
                        <div id="editInspectionTypeCheckboxes" class="d-flex"></div>
                    </div>

                    <!-- Start & End Datetime -->
                    <div class="mb-3">
                        <label for="edit_start_datetime" class="form-label">Start Date & Time</label>
                        <input type="datetime-local" class="form-control" id="edit_start_datetime" name="start_datetime" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_end_datetime" class="form-label">End Date & Time</label>
                        <input type="datetime-local" class="form-control" id="edit_end_datetime" name="end_datetime" required>
                    </div>

                    <!-- Locations -->
                    <div class="mb-3">
                        <label class="form-label">Select Locations</label>
                        <div id="editLocationCheckboxes"></div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="edit_note" class="form-label">Note (Optional)</label>
                        <textarea class="form-control" id="edit_note" name="note"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Update Mission</button>
                </form>
            </div>
        </div>
    </div>
</div>
    <!-- Add Mission Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5>Add Mission</h5>
        </div>
        <div class="card-body">
            <form id="addMissionForm">
                @csrf
            
                <div class="mb-3">
                    <label class="form-label">Select Inspection Types</label>
                    <div id="inspectionTypeCheckboxes" class="d-flex">
                        @foreach($inspectionTypes as $type)
                            <div class="form-check">
                                <input class="form-check-input inspection-type-checkbox" type="checkbox" name="inspection_types[]" value="{{ $type->id }}" id="inspection_{{ $type->id }}">
                                <label class="form-check-label" for="inspection_{{ $type->id }}">
                                    {{ $type->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                
            
                <div class="mb-3">
                    <label for="start_datetime" class="form-label">Start Date & Time</label>
                    <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" required>
                </div>
            
                <div class="mb-3">
                    <label for="end_datetime" class="form-label">End Date & Time</label>
                    <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" required>
                </div>
            
                <div class="mb-3">
                    <label class="form-label">Select Locations</label>
                    <div id="locationCheckboxes">
                        @foreach($locations as $location)
                            <div class="form-check">
                                <input class="form-check-input location-checkbox" type="checkbox" name="locations[]" value="{{ $location->id }}" id="location_{{ $location->id }}">
                                <label class="form-check-label" for="location_{{ $location->id }}">
                                    {{ $location->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            
                <div class="mb-3">
                    <label for="note" class="form-label">Note (Optional)</label>
                    <textarea class="form-control" id="note" name="note"></textarea>
                </div>
            
                <button type="submit" class="btn btn-success w-100">Create Mission</button>
            </form>
            
        </div>
    </div>

    <!-- Mission List Table -->
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Inspection Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Locations</th>
                <th>Note</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="missionTableBody">
            <tr>
                <td colspan="5" class="text-center text-muted">Loading missions...</td>
            </tr>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/missions.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
