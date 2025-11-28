export default () => ({
    recentLogs: [],
    form: {
        receivedTime: new Date().toISOString().slice(0, 16),
        silo: 'Silo 1 (Semen) - 48% Full',
        supplier: 'Holcim Indonesia',
        documentRef: '',
        netWeight: ''
    },
    isLoading: false,

    init() {
        this.fetchRecentLogs();
    },

    async fetchRecentLogs() {
        try {
            const response = await fetch('/api/receiving/recent');
            this.recentLogs = await response.json();
        } catch (error) {
            console.error('Error fetching receiving logs:', error);
        }
    },

    async submitForm() {
        this.isLoading = true;
        try {
            const response = await fetch('/api/receiving/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(this.form)
            });

            if (response.ok) {
                alert('Stock Added!');
                this.form.documentRef = '';
                this.form.netWeight = '';
                this.fetchRecentLogs();
            } else {
                alert('Failed to add stock.');
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            alert('Error submitting form.');
        } finally {
            this.isLoading = false;
        }
    }
});
