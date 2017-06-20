import {BackendService, backendService} from "./backend.service";
import {IncomingCallResendResponseInterface} from "./response/incoming-call-resend-response.interface";
import {AxiosResponse} from "axios";

/**
 * Сервис для работы с входящими звонками с АТС
 */
export class IncomingCallService {
    constructor(
        private backendService: BackendService
    ) { }

    /**
     * Переотправить входящий звонок арендатору
     */
    resend(callerId: string, customerId: number, comment: string = null): Promise<IncomingCallResendResponseInterface> {
        return this.backendService.makeRequest('POST', 'resend-incoming-call', {
            'incoming_call_resend': {
                customer: customerId,
                callerId: callerId,
                comment: comment
            }
        }).then((response: AxiosResponse) => {
            return response.data as IncomingCallResendResponseInterface;
        });
    }
}

export const incomingCallService = new IncomingCallService(backendService);
