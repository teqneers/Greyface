import {DateObject} from './common';

export interface Greylist {
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

export interface WhiteListEmail {
    email: string
}

export interface WhiteListDomain {
    domain: string
}

export interface BlackListEmail {
    email: string
}

export interface BlackListDomain {
    domain: string
}

export interface AutoWhiteListEmail {
    name: string,
    source: string,
    domain: string,
    firstSeen: DateObject,
    lastSeen: DateObject
}

export interface AutoWhiteListDomain {
    domain: string,
    source: string,
    firstSeen: DateObject,
    lastSeen: DateObject
}