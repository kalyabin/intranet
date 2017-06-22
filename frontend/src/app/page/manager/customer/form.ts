import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop} from "vue-property-decorator";
import {CustomerInterface} from "../../../service/model/customer.interface";
import {customerManagerService} from "../../../service/customer-manager.service";
import {CustomerResponseInterface} from "../../../service/response/customer-response.interface";
import {ValidationInterface} from "../../../service/response/validation.interface";

/**
 * Форма редактирования арендатора
 */
@Component({
    template: require('./form.html')
})
export class ManagerCustomerForm extends Vue {
    /**
     * Контрагент на входе
     */
    @Prop(Object) customer: CustomerInterface;

    /**
     * Идентификатор редактируемого контрагента
     */
    protected customerId: number = this.customer ? this.customer.id : 0;

    /**
     * Данные для редактирования
     */
    @Model() customerData: CustomerInterface = this.customer ? this.customer : {
        name: '',
        currentAgreement: ''
    };

    /**
     * Ожидание субмита формы
     */
    @Model() awaitOfSubmit: boolean = false;

    /**
     * Текст об ошибке
     */
    @Model() errorMessage: string = '';

    /**
     * Субмит формы
     */
    submit(): void {
        this.errorMessage = '';

        let isValidResponse = (response: ValidationInterface): boolean => {
            this.awaitOfSubmit = false;
            if (response.valid) {
                return true;
            }

            this.errorMessage = response.firstError;

            return false;
        };

        this.$validator.validateAll().then(() => {
            this.awaitOfSubmit = true;

            if (this.customerId) {
                customerManagerService
                    .update(this.customerId, this.customerData)
                    .then((response: CustomerResponseInterface) => {
                        if (isValidResponse(response)) {
                            this.$emit('customer:updated', response.customer);
                        }
                    }).catch(() => this.awaitOfSubmit = false);
            } else {
                customerManagerService
                    .create(this.customerData)
                    .then((response: CustomerResponseInterface) => {
                        if (isValidResponse(response)) {
                            this.$emit('customer:new', response.customer);
                        }
                    }).catch(() => {});
            }
        }).catch(() => {});
    }

    /**
     * Удаление контрагента
     */
    remove(): void {
        if (confirm('Арендатор будет удален безвозвратно, включая всех его пользователей. Вы уверены что хотите удалить арендатора?')) {
            this.awaitOfSubmit = true;
            this.errorMessage = '';
            customerManagerService
                .remove(this.customerId)
                .then((response: CustomerResponseInterface) => {
                    this.awaitOfSubmit = false;
                    if (response.success) {
                        this.$emit('customer:remove', this.customerId);
                    } else {
                        this.errorMessage = 'Не удалось удалить контрагента.';
                    }
                });
        }
    }
}
