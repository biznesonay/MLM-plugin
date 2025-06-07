
Новый
+50
-0

(function($){
    function startPayJob(users){
        $.post(mlmPayJob.ajaxurl, {action: 'start_all_circulation_job', users: users}, function(resp){
            if(typeof resp === 'string'){ try{ resp = JSON.parse(resp); }catch(e){} }
            if(resp && resp.status){
                localStorage.setItem('mlmPayJobId', resp.job_id);
                pollPayJob();
            }else{
                Swal.fire({icon: 'error', text: resp && resp.message ? resp.message : 'Error'});
            }
        });
    }

    function pollPayJob(){
        const jobId = localStorage.getItem('mlmPayJobId');
        if(!jobId) return;
        $.get(mlmPayJob.ajaxurl, {action: 'get_all_circulation_status', job_id: jobId}, function(resp){
            if(typeof resp === 'string'){ try{ resp = JSON.parse(resp); }catch(e){} }
            if(!resp) return;
            if(resp.status === 'running'){
                setTimeout(pollPayJob, 5000);
            }else if(resp.status === 'completed'){
                localStorage.removeItem('mlmPayJobId');
                if(resp.result){
                    Swal.fire({icon: resp.result.status ? 'success' : 'error', text: resp.result.message});
                    if(resp.result.status){ location.reload(); }
                }
            }else if(resp.status === 'failed'){
                localStorage.removeItem('mlmPayJobId');
                Swal.fire({icon: 'error', text: resp.result || 'Error'});
            }
        });
    }

    $(document).ready(function(){
        pollPayJob();
        $(document).on('click', '#pay', function(e){
            e.preventDefault();
            const users = [];
            $('.to-select input:checkbox:checked').each(function(){
                users.push($(this).val());
            });
            if(!users.length){
                Swal.fire({icon:'error', text:'No users selected'});
                return;
            }
            startPayJob(users);
        });
    });
})(jQuery);