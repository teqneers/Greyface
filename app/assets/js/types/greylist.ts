import {AuditTracking, HasId, DateObject} from './common';


export interface Greylist extends HasId, AuditTracking {
    aliasName: string,
    username: string,
    userId: string,
    connect: {
        name: string
        domain: string,
        source: string,
        rcpt: string,
        firstSeen: DateObject
    },
}
