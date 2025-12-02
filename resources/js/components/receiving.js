export default () => ({
    transactions: [],
    storageList: [],
    showForm: false,
    showSuccessModal: false,
    form: {
        receivedTime: '',
        storageId: '',
        supplier: '',
        documentRef: '',
        netWeight: ''
    },
    isLoading: false,

    init() {
        this.form.receivedTime = this.getLocalISOString();
        this.fetchStorageList();
        this.fetchTransactions();
    },

    getLocalISOString() {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        return now.toISOString().slice(0, 16);
    },

    async fetchStorageList() {
        try {
            const response = await fetch('/api/receiving/storage-list');
            const result = await response.json();
            this.storageList = result.data || [];

            // Set default storage if available
            if (this.storageList.length > 0 && !this.form.storageId) {
                this.form.storageId = this.storageList[0].inventoryId;
            }
        } catch (error) {
            console.error('Error fetching storage list:', error);
        }
    },

    async fetchTransactions() {
        try {
            const response = await fetch('/api/receiving/recent');
            const result = await response.json();
            this.transactions = result.data || [];
        } catch (error) {
            console.error('Error fetching transactions:', error);
        }
    },

    openForm() {
        this.showForm = true;
        this.form.receivedTime = this.getLocalISOString();
    },

    cancelForm() {
        this.showForm = false;
        this.resetForm();
    },

    resetForm() {
        this.form.documentRef = '';
        this.form.netWeight = '';
        this.form.supplier = '';
        if (this.storageList.length > 0) {
            this.form.storageId = this.storageList[0].inventoryId;
        }
    },

    async submitForm() {
        // Validation
        if (parseFloat(this.form.netWeight) < 0) {
            alert('Net Weight cannot be negative.');
            return;
        }

        this.isLoading = true;
        try {
            // Prepare payload matching API requirements
            const payload = {
                receivedTime: this.form.receivedTime + ':00.000Z',
                idInventory: parseInt(this.form.storageId),
                supplier: this.form.supplier,
                documentRef: this.form.documentRef,
                quantity: parseFloat(this.form.netWeight)
            };

            const response = await fetch('/api/receiving/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            if (response.ok) {
                // Optimistic Update: Add new record to top of list immediately
                const silo = this.storageList.find(s => s.inventoryId === payload.idInventory);
                this.transactions.unshift({
                    receivedTime: payload.receivedTime,
                    storageName: silo ? silo.name : 'Unknown Silo',
                    supplier: payload.supplier,
                    documentRef: payload.documentRef,
                    quantity: payload.quantity,
                    status: 'Completed' // Assume success since we got 200 OK
                });

                this.resetForm();
                this.showForm = false;
                this.showSuccessModal = true;

                // Fetch latest data in background after a delay to allow indexing
                setTimeout(() => this.fetchTransactions(), 2000);
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
