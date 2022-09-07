import React from "react";

import {config as buildConfig} from "@neos-project/build-essentials/src/styles/styleConstants";
import {Button, TextInput, SelectBox} from '@neos-project/react-ui-components';

import {PATTERN_INTERVAL} from "./constants";

export class IntervalEditor extends React.Component {
    render() {
        const {i18n} = this.props;
        if (!this.props.value) {
            return (
                <div>
                    <p>{i18n.translate('Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.message.empty')}</p>
                    <Button onClick={() => this.props.commit('P1M')}>
                        {i18n.translate('Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.action.initialize')}
                    </Button>
                </div>
            );
        }

        const result = this.props.value.match(PATTERN_INTERVAL) || [];
        const dateOrTime = result[1] || 'P';
        const amount = result[2] || '1';
        const unit = result[3] || 'M';

        return (
            <div>
                <div style={{display: 'flex', marginBottom: buildConfig.spacing.full}}>
                    <Button
                        onClick={() => {
                            const amountAsNumber = parseInt(amount, 10);

                            if (amountAsNumber > 1) {
                                this.props.commit(`${dateOrTime}${amountAsNumber - 1}${unit}`)
                            }
                        }}
                    >
                        -
                    </Button>
                    <TextInput
                        style={{textAlign: 'center'}}
                        value={amount}
                        onChange={amount => {
                            if (/^\d+$/.test(amount)) {
                                this.props.commit(`${dateOrTime}${amount}${unit}`)
                            }
                        }}
                    />
                    <Button
                        onClick={() => {
                            const amountAsNumber = parseInt(amount, 10);

                            this.props.commit(`${dateOrTime}${amountAsNumber + 1}${unit}`)
                        }}
                    >
                        +
                    </Button>
                </div>
                <div>
                    <SelectBox
                        style={{minWidth: 0}}
                        onValueChange={({dateOrTime, unit}) => this.props.commit(`${dateOrTime}${amount}${unit}`)}
                        value={{dateOrTime, unit}}
                        options={[
                            {
                                label: i18n.translate(amount === '1'
                                    ? 'Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.unit.minute'
                                    : 'Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.unit.minutes'),
                                value: {dateOrTime: 'PT', unit: 'M'}
                            },
                            {
                                label: i18n.translate(amount === '1'
                                    ? 'Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.unit.hour'
                                    : 'Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.unit.hours'),
                                value: {dateOrTime: 'PT', unit: 'H'}
                            },
                            {
                                label: i18n.translate(amount === '1'
                                    ? 'Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.unit.day'
                                    : 'Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.unit.days'),
                                value: {dateOrTime: 'P', unit: 'D'}
                            },
                            {
                                label: i18n.translate(amount === '1'
                                    ? 'Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.unit.week'
                                    : 'Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.unit.weeks'),
                                value: {dateOrTime: 'P', unit: 'W'}
                            },
                            {
                                label: i18n.translate(amount === '1'
                                    ? 'Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.unit.month'
                                    : 'Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.unit.months'),
                                value: {dateOrTime: 'P', unit: 'M'}
                            },
                            {
                                label: i18n.translate(amount === '1'
                                    ? 'Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.unit.year'
                                    : 'Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.unit.years'),
                                value: {dateOrTime: 'P', unit: 'Y'}
                            },
                        ]}
                    />
                </div>
                {(this.props.options || {}).allowEmpty ? (
                    <div>
                        <Button onClick={() => this.props.commit('')}>
                            {i18n.translate('Sitegeist.Bitzer:NeosUiPlugin:intervalEditor.action.clear')}
                        </Button>
                    </div>
                ) : null}
            </div>
        );
    }
}
