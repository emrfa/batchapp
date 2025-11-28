<h1>Factory Dashboard Test</h1>
<ul>
    @foreach($batches as $batch)
        <li>
            <strong>Batch ID:</strong> {{ $batch->idBatch }} | 
            <strong>Time:</strong> {{ $batch->batchTime }} | 
            <strong>Mixer:</strong> {{ $batch->mixerCode }}
        </li>
    @endforeach
</ul>