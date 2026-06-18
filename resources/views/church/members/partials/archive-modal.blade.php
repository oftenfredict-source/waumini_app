<div class="modal fade" id="archiveMemberModal-{{ $member->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('church.members.archive', $member) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-archive"></i> Archive Member</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        You are archiving <strong>{{ $member->full_name }}</strong> ({{ $member->member_number }}).
                        They will be moved to the archived list and will not be able to log in.
                    </p>
                    <div class="form-group mb-0">
                        <label for="archive_reason_{{ $member->id }}">Reason for archiving <span class="text-danger">*</span></label>
                        <textarea id="archive_reason_{{ $member->id }}" name="archive_reason" class="form-control" rows="4"
                                  required minlength="3" maxlength="1000"
                                  placeholder="e.g. Relocated to another church, requested removal, duplicate record...">{{ old('archive_reason') }}</textarea>
                        @error('archive_reason')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-archive"></i> Archive Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
